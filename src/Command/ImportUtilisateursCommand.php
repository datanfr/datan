<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère **tous** les comptes de connexion de la base de production.
 *
 * Ces comptes ne sont dans aucun dépôt ouvert : à préserver au même titre que
 * les décryptages. **Le mot de passe est repris tel quel** — le legacy le hache
 * en `password_hash(PASSWORD_DEFAULT)`, du bcrypt (`$2y$…`) que le vérifieur
 * `auto` de Symfony relit, puis ré-encode de lui-même à la première connexion.
 * Chacun se connecte donc sur le nouveau site avec son mot de passe actuel.
 *
 * Le `type` de l'ancienne base décide du rôle :
 *   - `admin`  → ROLE_ADMIN (rédaction) ;
 *   - `writer` → rédacteur (aucun rôle explicite, ROLE_REDACTEUR par défaut) ;
 *   - `mp`     → rattaché au député via `users_mp.mpId`, d'où ROLE_DEPUTE ;
 *   - tout le reste, et d'abord le type vide des lecteurs inscrits sur le site
 *     → **ROLE_LECTEUR**, posé explicitement. Le défaut est le rôle le plus
 *     faible et non l'inverse : un compte sans député ni rôle deviendrait
 *     rédacteur par le repli de `Utilisateur::getRoles()`, et un type inconnu
 *     n'a pas à ouvrir la rédaction. Le lecteur retrouve ce que le legacy lui
 *     offrait — son compte, son code postal, son mot de passe, la suppression
 *     du compte (`MonCompteController`) — et rien de plus.
 *
 * Un compte « mp » dont le député manque à la base n'est pas écarté : il entre
 * comme lecteur, et le dit. Il se connecte, sans espace député ; un rejeu après
 * l'import du député le rattache.
 *
 * `users.id` est gardé en `utilisateur.id_origine` : c'est par lui que le legacy
 * désigne l'auteur d'un décryptage ou le compte d'un abonné. Il sert aussi de
 * clé quand un identifiant de connexion a changé d'une récupération à l'autre.
 * Le code postal (`zipcode`, demandé à l'inscription) est repris pour tous.
 *
 * Requête d'export : `bin/recuperation/legacy/utilisateurs.sql`.
 *
 * Import de récupération : il réaligne mot de passe, rôle et rattachement sur
 * la production. Après la bascule, un rejeu rendrait l'ancien mot de passe à
 * qui l'a changé ici.
 */
#[AsCommand(
    name: 'app:import:utilisateurs',
    description: 'Récupère les comptes de connexion (rédaction, députés, lecteurs) depuis un export TSV de la production.',
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
        // Comptes déjà récupérés, pour suivre un changement d'identifiant.
        $identifiantParOrigine = $this->connection->fetchAllKeyValue('SELECT id_origine, identifiant FROM utilisateur WHERE id_origine IS NOT NULL');

        $lignes = [];
        $ecartes = ['sans identifiant ni mot de passe' => 0];
        $parFamille = ['admin' => 0, 'rédacteur' => 0, 'député' => 0, 'lecteur' => 0];
        $deputesAbsents = 0;
        $renommes = 0;

        foreach ($this->lignes($fichier) as $ligne) {
            [$idOrigine, $identifiant, $nom, $email, $type, $motDePasse, $creeLe, $codePostal, $mpId] = array_pad($ligne, 9, null);

            $identifiant = $this->texte($identifiant);
            $motDePasse = $this->texte($motDePasse);
            $idOrigine = $this->entier($idOrigine);

            if ($identifiant === null || $motDePasse === null) {
                ++$ecartes['sans identifiant ni mot de passe'];

                continue;
            }

            $type = (string) $this->texte($type);
            $deputeId = null;

            if ($type === 'mp') {
                $mpId = $this->texte($mpId);
                $deputeId = $mpId === null ? null : ($deputeParMpId[$mpId] ?? null);

                if ($deputeId === null) {
                    ++$deputesAbsents;
                    $io->warning(sprintf('Compte n° %d (député %s) repris comme lecteur : ce député n\'est pas en base.', $idOrigine, $mpId ?? '?'));
                }
            }

            [$roles, $famille] = match (true) {
                $type === 'admin' => [[Utilisateur::ROLE_ADMIN], 'admin'],
                $type === 'writer' => [[], 'rédacteur'],
                $deputeId !== null => [[], 'député'],
                default => [[Utilisateur::ROLE_LECTEUR], 'lecteur'],
            };
            ++$parFamille[$famille];

            // L'identifiant a changé au legacy depuis la dernière récupération :
            // on renomme la ligne existante, sans quoi l'upsert sur l'identifiant
            // créerait un second compte et buterait sur l'unicité d'id_origine.
            $ancien = $idOrigine === null ? null : ($identifiantParOrigine[$idOrigine] ?? null);
            if ($ancien !== null && $ancien !== $identifiant) {
                $this->connection->executeStatement(
                    'UPDATE utilisateur SET identifiant = ? WHERE id_origine = ?',
                    [$identifiant, $idOrigine],
                );
                ++$renommes;
            }

            $lignes[] = [
                $identifiant,
                $this->texte($nom) ?? $identifiant,
                $this->texte($email),
                json_encode($roles),
                $motDePasse,
                $this->dateHeure($creeLe),
                $deputeId,
                $idOrigine,
                $this->codePostal($codePostal),
            ];
        }

        if ($lignes !== []) {
            // Mot de passe, rôle et rattachement se réalignent sur la production à
            // chaque exécution — c'est une récupération, la production fait foi.
            // `cree_le` n'est pas réécrit : la date d'origine du compte demeure.
            $this->upsert(
                'utilisateur',
                ['identifiant', 'nom', 'email', 'roles', 'mot_de_passe', 'cree_le', 'depute_id', 'id_origine', 'code_postal'],
                $lignes,
                ['nom', 'email', 'roles', 'mot_de_passe', 'depute_id', 'id_origine'],
                ['code_postal'],
            );
        }

        $this->consigner($io, 'Comptes', $fichier, \count($lignes), $ecartes);
        $io->text(vsprintf('Par famille : %d admin, %d rédacteur(s), %d député(s), %d lecteur(s).', array_values($parFamille)));

        if ($deputesAbsents > 0) {
            $io->text(sprintf('%d compte(s) de député repris comme lecteur, faute du député en base.', $deputesAbsents));
        }
        if ($renommes > 0) {
            $io->text(sprintf('%d identifiant(s) de connexion renommé(s) comme au legacy.', $renommes));
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

    /** `zipcode` est un VARCHAR(255) libre au legacy ; la colonne locale en tient 10. */
    private function codePostal(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null ? null : mb_substr(trim($valeur), 0, 10);
    }
}
