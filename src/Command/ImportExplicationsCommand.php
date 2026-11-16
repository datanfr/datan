<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les explications de vote des députés depuis la base de production.
 *
 * C'est du contenu éditorial, à préserver comme les décryptages : un import qui
 * en perd est un import qui perd de la donnée irremplaçable.
 *
 * Le scrutin se résout **par le décryptage**, pas par le numéro brut. Un vote du
 * Congrès porte dans `explications_mp` le numéro sentinelle `-1` (sa
 * numérotation entre en collision avec celle de l'Assemblée) : chercher un
 * `scrutin.numero = -1` ne trouve rien, et l'import précédent
 * (`app:import:annexes`, apparié sur l'uid) laissait ainsi filer les cinq
 * explications de l'inscription de l'IVG dans la Constitution. La table
 * `decryptage` porte, elle, le même `-1` **et** le bon `scrutin_id` : on passe
 * par elle. C'est cohérent avec la règle du site — on n'explique qu'un vote
 * décrypté.
 *
 * Requête d'export : `bin/recuperation/legacy/explications.sql`. À jouer après
 * `app:import:decryptages`, dont elle lit les rattachements.
 */
#[AsCommand(
    name: 'app:import:explications',
    description: 'Récupère les explications de vote des députés depuis un export TSV de la production.',
)]
class ImportExplicationsCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'Export TSV des explications', 'var/legacy/explications.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fichier = (string) $input->getOption('fichier');

        $deputeParMpId = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute WHERE mp_id IS NOT NULL');

        // Résolution du scrutin : le décryptage d'abord (il gère le -1 du
        // Congrès), le scrutin ensuite pour les votes non décryptés.
        $scrutinParDecryptage = [];
        foreach ($this->connection->iterateAssociative('SELECT legislature, vote_numero, scrutin_id FROM decryptage WHERE scrutin_id IS NOT NULL') as $d) {
            $scrutinParDecryptage[$d['legislature'] . '|' . $d['vote_numero']] = (int) $d['scrutin_id'];
        }
        $scrutinParNumero = [];
        foreach ($this->connection->iterateAssociative('SELECT legislature, numero, id FROM scrutin') as $s) {
            $scrutinParNumero[$s['legislature'] . '|' . $s['numero']] = (int) $s['id'];
        }

        $lignes = [];
        $ecartes = ['depute_absent' => 0, 'scrutin_absent' => 0, 'vide' => 0];

        foreach ($this->lignes($fichier) as $ligne) {
            [$mpId, $legislature, $numero, $texte, $etat, $creeLe, $modifieLe] = array_pad($ligne, 7, null);

            $deputeId = ($mp = $this->texte($mpId)) === null ? null : ($deputeParMpId[$mp] ?? null);
            $cle = $legislature . '|' . $numero;
            $scrutinId = $scrutinParDecryptage[$cle] ?? $scrutinParNumero[$cle] ?? null;

            if ($texte === null || trim($texte) === '') {
                ++$ecartes['vide'];

                continue;
            }
            if ($deputeId === null) {
                ++$ecartes['depute_absent'];

                continue;
            }
            if ($scrutinId === null) {
                ++$ecartes['scrutin_absent'];
                $io->warning(sprintf('Explication de %s sur L%s n°%s écartée : scrutin introuvable.', $mp, $legislature, $numero));

                continue;
            }

            $lignes[] = [
                $scrutinId,
                $deputeId,
                $texte,
                $etat === '1' ? 1 : 0,
                $this->dateHeure($creeLe),
                $this->dateHeure($modifieLe),
            ];
        }

        if ($lignes !== []) {
            $this->upsert(
                'explication',
                ['scrutin_id', 'depute_id', 'texte', 'publiee', 'created_at', 'modified_at'],
                $lignes,
                ['texte', 'publiee', 'modified_at'],
            );
        }

        $this->consigner($io, 'Explications de vote', $fichier, \count($lignes), [
            'texte vide' => $ecartes['vide'],
            'député absent' => $ecartes['depute_absent'],
            'scrutin absent' => $ecartes['scrutin_absent'],
        ]);

        return Command::SUCCESS;
    }

    private function dateHeure(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
