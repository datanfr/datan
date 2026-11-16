<?php

namespace App\Command;

use App\Http\RechauffeurCache;
use App\Tricoteuses\Moissonneur;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionLost;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Mise à jour quotidienne de la base depuis les données des Tricoteuses.
 *
 * C'est le point d'entrée unique à planifier. L'ordre des étapes suit les
 * dépendances : un scrutin se rattache à un dossier et ventile ses votes par
 * groupe, il faut donc que dossiers et organes soient connus avant lui.
 *
 * Trois garanties, chacune née d'un incident de production (journal
 * `sync-quotidien.log`, 27/10 au 15/11) :
 *
 * - **Le delta n'est acquitté qu'après une chaîne entièrement réussie.** Une
 *   étape qui échoue laisse la moisson en attente, et la synchronisation
 *   suivante la reprend avec la sienne ({@see Moissonneur::acquitte()}).
 * - **Une synchronisation qui a commencé d'écrire purge le cache HTTP**, qu'elle
 *   réussisse ou non : les pages restent en cache un jour, et un import à
 *   moitié fait reste un import visible à moitié sinon. Les pages figées, elles,
 *   ne sont vidées que si les données closes ont bougé (`EmpreinteFigee`).
 * - **Elle tient sur un hébergement mutualisé hostile** : ni le Hangup, ni le
 *   `wait_timeout` de 260 s d'o2switch ne doivent plus l'arrêter en route.
 *
 * À lancer impérativement en APP_ENV=prod : en dev, le journal Doctrine
 * conserve chaque requête en mémoire et fait échouer les imports volumineux.
 */
#[AsCommand(
    name: 'app:sync:quotidien',
    description: 'Moissonne les Tricoteuses et met à jour la base (à planifier quotidiennement).',
)]
class SyncQuotidienCommand extends Command
{
    /** Code de sortie quand une autre synchronisation tient le verrou (EX_TEMPFAIL). */
    public const DEJA_EN_COURS = 75;

    /**
     * Les étapes, dans l'ordre des dépendances.
     *
     * Toute commande qui lit le delta d'une moisson doit être ici, et non à la
     * suite dans la crontab : le delta est acquitté à la fin de cette chaîne,
     * et une commande lancée après n'aurait plus la garantie de le revoir si
     * elle échouait.
     *
     * @var list<array{0: string, 1: array<string, mixed>, 2: string}>
     */
    private const ETAPES = [
        ['app:tricoteuses:sync', [], 'Moisson des dépôts'],
        ['app:import:acteurs', [], 'Organes, députés, mandats et fonctions'],
        // Après les acteurs, dont elle a besoin pour rattacher chaque mandat à sa
        // fiche : les délégations du Bureau (« Postes Assemblée »), qui se créent
        // et s'éteignent en cours de législature.
        ['app:import:organes', [], 'Organes hors table dédiée (délégations du Bureau)'],
        // Mandats COMPER. Longtemps lancée après le sync, dans la crontab : elle
        // lit pourtant le même delta des acteurs, et l'acquittement suppose que
        // toutes ses lectrices soient passées.
        ['app:import:commissions', [], 'Commissions permanentes'],
        // Juste après les acteurs : les profils se lisent dans les mêmes
        // fichiers, et c'est pour qu'ils se rafraîchissent qu'ils ont quitté la
        // base gelée dont ils venaient.
        ['app:import:profils-sociaux', [], 'Profils socio-professionnels'],
        ['app:import:photos', [], 'Photographies des députés'],
        // Dans la foulée : une photo publiée sans son détourage sortirait avec
        // le fond de studio bleu au milieu d'une carte blanche. L'étape ne
        // retraite que ce que la précédente vient d'écrire — 647 portraits déjà
        // faits coûtent le temps d'un `filemtime()` chacun.
        ['app:photos:detourer', [], 'Détourage des photographies'],
        ['app:import:dossiers', [], 'Dossiers législatifs'],
        // Avant les scrutins : c'est au scrutin de désigner l'amendement qu'il
        // met aux voix, l'amendement doit donc déjà exister.
        ['app:import:amendements', [], 'Amendements'],
        // L'auteur de chaque amendement, pour la carte « L'auteur de
        // l'amendement » de la page de vote. Restée hors chaîne jusqu'au 16/11 :
        // 1 297 scrutins d'amendement de la 17e s'affichaient sans elle.
        ['app:import:auteurs-amendements', [], 'Auteurs des amendements'],
        ['app:import:scrutins', [], 'Scrutins, ventilations et votes'],
        // Les comptes rendus paraissent quelques jours après la séance ; le
        // delta quotidien n'en touche qu'une poignée. Ils nourrissent le
        // brouillon de décryptage par IA de l'espace de rédaction.
        ['app:import:comptes-rendus', [], 'Comptes rendus des séances'],
        // Après les scrutins, puisqu'elle apparie les deux. Elle ne sort pas
        // sur le réseau : tout se joue sur les dépôts déjà clonés. Le rattrapage
        // par scraping (`app:scraper:scrutins`) reste hors de cette chaîne —
        // c'est la seule étape qui interroge un site public, et elle mérite
        // d'être lancée sciemment.
        ['app:lien:scrutins', [], 'Rattachement des scrutins à leur amendement'],
        // En dernier : les classements se calculent sur les votes du jour.
        ['app:calcul:classements', [], 'Classements des députés et des groupes'],
    ];

    /**
     * Le `wait_timeout` du MariaDB d'o2switch est de 260 s. Une étape qui lit
     * des milliers de fichiers entre deux requêtes le dépasse — les 10 824
     * organes d'`app:import:acteurs`, les 9 895 scrutins relus par
     * `app:lien:scrutins` avant les classements — et la requête suivante tombe
     * sur « MySQL server has gone away » : 01/11, 08/11, 10/11, 14/11. Toutes
     * les étapes partagent la connexion de ce processus : la relever une fois
     * suffit.
     */
    private const ATTENTE_SQL = 28800;

    public function __construct(
        private readonly Moissonneur $moissonneur,
        private readonly Connection $connection,
        private readonly RechauffeurCache $rechauffeur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('tout', null, InputOption::VALUE_NONE, 'Réimporte l\'intégralité des dépôts au lieu du seul delta');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tout = (bool) $input->getOption('tout');
        $debut = microtime(true);

        $io->title(sprintf('Mise à jour quotidienne — %s', $this->heure('d/m/Y H:i:s')));

        if (!$this->moissonneur->verrouille()) {
            $io->warning('Une synchronisation est déjà en cours (verrou .datan-sync.lock) : celle-ci s\'abstient.');

            return self::DEJA_EN_COURS;
        }

        // Cinq exécutions sur vingt ont été tuées par un SIGHUP dans les
        // minutes qui suivent 6 h (29/10, 30/10, 31/10, 13/11, 15/11) — la
        // fenêtre où CloudLinux regénère la configuration du sélecteur PHP
        // (php-selecteur.log, 06:03 à 06:08). Une tâche cron n'a pas de
        // terminal : ce signal ne lui est pas destiné.
        // La crontab l'ignore déjà (`trap '' HUP`) ; ceci couvre le lancement
        // à la main dans une session SSH qui se ferme.
        if (\function_exists('pcntl_signal')) {
            pcntl_signal(\SIGHUP, \SIG_IGN);
        }

        $aEcrit = false;

        try {
            $this->connection->executeStatement(sprintf('SET SESSION wait_timeout = %d', self::ATTENTE_SQL));

            $moissons = null;

            foreach (self::ETAPES as [$nom, $arguments, $libelle]) {
                $io->section(sprintf('%s — %s', $libelle, $this->heure('H:i:s')));

                $commande = $this->getApplication()?->find($nom);
                if ($commande === null) {
                    $io->error(sprintf('Commande « %s » introuvable.', $nom));

                    return Command::FAILURE;
                }
                // Les commandes sont chargées paresseusement : sans ce
                // déballage, la moisson n'est jamais reconnue comme telle et
                // rien ne s'acquitte — l'échec serait silencieux, le delta
                // grossissant simplement de jour en jour.
                if ($commande instanceof LazyCommand) {
                    $commande = $commande->getCommand();
                }

                if ($tout && $commande->getDefinition()->hasOption('tout')) {
                    $arguments['--tout'] = true;
                }

                // Toute étape après la moisson peut écrire en base.
                $aEcrit = $aEcrit || !$commande instanceof TricoteusesSyncCommand;

                $code = $this->lance($commande, $arguments, $output, $io);

                if ($code !== Command::SUCCESS) {
                    $io->error(sprintf(
                        'Étape « %s » en échec (code %d) — mise à jour interrompue. Rien n\'est acquitté : la prochaine synchronisation reprendra ce delta.',
                        $nom,
                        $code,
                    ));

                    return $code;
                }

                if ($commande instanceof TricoteusesSyncCommand) {
                    $moissons = $commande->moissons();
                }
            }

            $this->acquitte($moissons ?? [], $io);
        } catch (\Throwable $e) {
            // Rendue lisible ici plutôt que par le gestionnaire de la console :
            // le journal doit dire quelle étape est tombée, pas seulement la
            // pile d'appels.
            $io->error(sprintf('Mise à jour interrompue par une exception (%s) : %s', $e::class, $e->getMessage()));

            return Command::FAILURE;
        } finally {
            if ($aEcrit) {
                // Les pages figées ne sont vidées que si le passé a bougé :
                // toujours après une reconstruction complète, sinon quand
                // l'empreinte des données closes n'est plus celle de la veille.
                if ($tout) {
                    $this->rechauffeur->purger(tout: true);
                    $figees = true;
                } else {
                    $figees = $this->rechauffeur->purgerApresSynchronisation();
                }
                $io->text(sprintf(
                    'Cache HTTP vidé (%s), %s — le réchauffage suit dans la crontab.',
                    $this->heure('H:i:s'),
                    $figees ? 'pages figées comprises : les données closes ont changé' : 'pages figées conservées',
                ));
            }
            $this->moissonneur->deverrouille();
        }

        $io->success(sprintf('Base à jour en %d s.', (int) (microtime(true) - $debut)));

        return Command::SUCCESS;
    }

    /**
     * Lance une étape. Une connexion perdue en cours de route vaut une seconde
     * tentative, après reconnexion : les imports sont des upserts, rejouer une
     * étape ne fausse rien, et attendre le lendemain coûte une journée de
     * scrutins invisibles.
     *
     * @param array<string, mixed> $arguments
     */
    private function lance(Command $commande, array $arguments, OutputInterface $output, SymfonyStyle $io): int
    {
        try {
            return $commande->run(new ArrayInput($arguments), $output);
        } catch (ConnectionLost $e) {
            $io->warning(sprintf('Connexion à la base perdue (%s) — reconnexion et seconde tentative de l\'étape.', $e->getMessage()));
            $this->connection->close();
            $this->connection->executeStatement(sprintf('SET SESSION wait_timeout = %d', self::ATTENTE_SQL));

            return $commande->run(new ArrayInput($arguments), $output);
        }
    }

    /**
     * @param list<\App\Tricoteuses\Moisson> $moissons
     */
    private function acquitte(array $moissons, SymfonyStyle $io): void
    {
        $lignes = [];
        foreach ($moissons as $moisson) {
            $lignes[] = [
                $moisson->depot->nom,
                substr($moisson->commitApres, 0, 8),
                $this->moissonneur->acquitte($moisson) ? 'acquitté' : 'NON acquitté : le dépôt a bougé depuis la moisson',
            ];
        }

        if ($lignes !== []) {
            $io->section('Acquittement des moissons');
            $io->table(['Dépôt', 'Commit', 'Delta'], $lignes);
        }
    }

    /**
     * L'heure de Paris, quel que soit le fuseau de PHP : celui du serveur est
     * UTC, et le journal titrait « 05:00 » une exécution lancée par la
     * crontab à 6 h.
     */
    private function heure(string $format): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))->format($format);
    }
}
