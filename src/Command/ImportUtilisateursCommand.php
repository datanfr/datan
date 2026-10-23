<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les comptes de connexion de la base de production.
 *
 * Ces comptes ne sont dans aucun dépôt ouvert : ce sont ceux de la rédaction et
 * des députés, à préserver au même titre que les décryptages. **Le mot de passe
 * est repris tel quel** — le legacy le hache en `password_hash(PASSWORD_DEFAULT)`,
 * du bcrypt (`$2y$…`) que le vérifieur `auto` de Symfony relit sans que
 * personne n'ait à le réinitialiser. Le compte de l'ancienne base se connecte
 * donc sur la nouvelle avec le même mot de passe.
 *
 * Le `type` de l'ancienne base décide du rôle :
 *   - `admin`  → ROLE_ADMIN (rédaction) ;
 *   - `writer` → rédacteur (aucun rôle explicite, ROLE_REDACTEUR par défaut) ;
 *   - `mp`     → rattaché au député via `users_mp.mpId`, d'où ROLE_DEPUTE ;
 *   - `''`     → **lecteur public : écarté**. L'espace lecteur n'est pas porté,
 *     et un compte sans député ni rôle deviendrait rédacteur par le jeu de
 *     `Utilisateur::getRoles()` — on n'ouvre pas la rédaction à d'anciens
 *     inscrits.
 *
 * Régénérer l'export (une seule ligne, le `users_mp` porte le lien député) :
 *
 *   docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *     "SELECT u.username, u.name, u.email, u.type, u.password, u.registration_date, um.mpId \
 *      FROM users u LEFT JOIN users_mp um ON um.user = u.id" \
 *     > var/legacy/utilisateurs.tsv
 */
#[AsCommand(
    name: 'app:import:utilisateurs',
    description: 'Récupère les comptes de connexion (rédaction, députés) depuis un export TSV de la production.',
)]
class ImportUtilisateursCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'Export TSV des comptes', 'var/legacy/utilisateurs.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fichier = (string) $input->getOption('fichier');

        // mp_id → id du député : le rattachement d'un compte « mp » passe par là.
        $deputeParMpId = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute WHERE mp_id IS NOT NULL');

        $lignes = [];
        $ecartes = ['lecteur' => 0, 'sans_identifiant' => 0, 'depute_absent' => 0];

        foreach ($this->lignes($fichier) as $ligne) {
            [$identifiant, $nom, $email, $type, $motDePasse, $creeLe, $mpId] = array_pad($ligne, 7, null);

            $identifiant = $this->texte($identifiant);
            $motDePasse = $this->texte($motDePasse);

            if ($identifiant === null || $motDePasse === null) {
                ++$ecartes['sans_identifiant'];

                continue;
            }

            // Lecteur public : pas de place pour lui, et surtout pas dans la rédaction.
            if ($this->texte($type) === null || $type === 'reader') {
                ++$ecartes['lecteur'];

                continue;
            }

            $deputeId = null;

            if ($type === 'mp') {
                $mpId = $this->texte($mpId);
                $deputeId = $mpId === null ? null : ($deputeParMpId[$mpId] ?? null);

                if ($deputeId === null) {
                    // Un compte de député dont le député n'est pas dans notre base
                    // deviendrait rédacteur : on l'écarte et on le dit.
                    ++$ecartes['depute_absent'];
                    $io->warning(sprintf('Compte « %s » (député %s) écarté : ce député n\'est pas en base.', $identifiant, $mpId ?? '?'));

                    continue;
                }
            }

            $lignes[] = [
                $identifiant,
                $this->texte($nom) ?? $identifiant,
                $this->texte($email),
                $type === 'admin' ? '["ROLE_ADMIN"]' : '[]',
                $motDePasse,
                $this->dateHeure($creeLe),
                $deputeId,
            ];
        }

        if ($lignes !== []) {
            // Le mot de passe et le rôle se réalignent sur la production à chaque
            // exécution — c'est une récupération, la production fait foi. `cree_le`
            // n'est pas réécrit : la date d'origine du compte est conservée.
            $this->upsert(
                'utilisateur',
                ['identifiant', 'nom', 'email', 'roles', 'mot_de_passe', 'cree_le', 'depute_id'],
                $lignes,
                ['nom', 'email', 'roles', 'mot_de_passe', 'depute_id'],
            );
        }

        $io->success(sprintf('%d compte(s) récupéré(s).', \count($lignes)));

        if (array_sum($ecartes) > 0) {
            $io->text(sprintf(
                'Écartés : %d lecteur(s) public(s), %d sans identifiant, %d député(s) absent(s) de la base.',
                $ecartes['lecteur'],
                $ecartes['sans_identifiant'],
                $ecartes['depute_absent'],
            ));
        }

        return Command::SUCCESS;
    }

    /** `registration_date` de la production, ou maintenant si l'export ne la porte pas. */
    private function dateHeure(?string $valeur): string
    {
        $valeur = $this->texte($valeur);

        if ($valeur === null || str_starts_with($valeur, '0000')) {
            return (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        }

        return $valeur;
    }
}
