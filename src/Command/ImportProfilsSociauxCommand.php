<?php

namespace App\Command;

use App\FamilleSocioPro;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe le profil socio-professionnel des députés — date de naissance, métier
 * exercé avant l'élection et rangs de la nomenclature INSEE — depuis l'état
 * civil et la profession publiés dans le dépôt des acteurs.
 *
 * Ces trois données alimentent les classements par âge et par origine sociale
 * ({@see \App\Entity\ProfilSocial}).
 *
 * La source était jusqu'ici un export de la base de référence canutes, un dump
 * figé que la moisson quotidienne ne traverse pas : les profils n'y auraient
 * jamais été rafraîchis. Les mêmes champs sont dans `acteurs/*.json`, sous
 * `etatCivil.infoNaissance.dateNais` et `profession`, et pour un contenu
 * rigoureusement identique — canutes les tenait de la même source.
 *
 * `depute.cat_soc_pro` reste hors de cet import comme de celui des acteurs :
 * cette colonne est un SMALLINT hérité de l'application d'origine, alors que
 * l'open data — comme la base de production — ne publie de la catégorie
 * socio-professionnelle que son libellé. C'est `profil_social.cat_soc_pro` qui
 * porte ce libellé et qui est tenue à jour ici.
 */
#[AsCommand(
    name: 'app:import:profils-sociaux',
    description: 'Importe la date de naissance et la catégorie socio-professionnelle des députés.',
)]
class ImportProfilsSociauxCommand extends ImportTricoteusesCommand
{
    private const COLONNES = ['depute_id', 'date_naissance', 'metier', 'cat_soc_pro', 'fam_soc_pro'];

    protected function depotParDefaut(): string
    {
        return 'acteurs';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tout = (bool) $input->getOption('tout');
        $chemin = $this->chemin($input);

        $io->title('Import des profils socio-professionnels');
        $io->text($this->incremental($chemin, $tout) ? 'Régime incrémental : seuls les fichiers modifiés.' : 'Import complet du dépôt.');

        $idDeputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        $lus = 0;
        $inconnus = 0;
        $sansFamille = 0;
        $lot = [];

        foreach ($this->fichiers($chemin, 'acteurs', $tout) as $fichier) {
            $acteur = $this->lisJson($fichier);
            if ($acteur === null || !isset($acteur['uid'])) {
                continue;
            }
            ++$lus;

            // Sénateurs et ministres partagent le dépôt : sans fiche de député,
            // il n'y a pas de profil à tenir.
            $deputeId = $idDeputes[$acteur['uid']] ?? null;
            if ($deputeId === null) {
                ++$inconnus;
                continue;
            }

            $profession = $acteur['profession'] ?? [];
            $famille = FamilleSocioPro::normalise($profession['socProcInsee']['famSocPro'] ?? null);

            if ($famille === null) {
                ++$sansFamille;
            }

            $lot[] = [
                $deputeId,
                // La date est tantôt horodatée (« 1939-06-10T00:00:00+01:00 »),
                // tantôt nue selon les fiches.
                $this->date($acteur['etatCivil']['infoNaissance']['dateNais'] ?? null),
                $this->texte($profession['libelleCourant'] ?? null),
                $this->texte($profession['socProcInsee']['catSocPro'] ?? null),
                $famille,
            ];

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->enregistre($lot);
                $lot = [];
            }
        }

        $this->enregistre($lot);

        if ($inconnus > 0) {
            $io->text(sprintf('%d acteurs ignorés : sans fiche de député.', $inconnus));
        }

        $enBase = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS profils, COUNT(date_naissance) AS naissances,
                    COUNT(metier) AS metiers, COUNT(cat_soc_pro) AS categories,
                    COUNT(fam_soc_pro) AS familles
             FROM profil_social',
        ) ?: [];

        $io->success(sprintf(
            '%d acteurs lus — %d profils en base : %d dates de naissance, %d métiers, '
            . '%d catégories INSEE, %d rangés dans une famille socio-professionnelle (%d sans).',
            $lus,
            $enBase['profils'] ?? 0,
            $enBase['naissances'] ?? 0,
            $enBase['metiers'] ?? 0,
            $enBase['categories'] ?? 0,
            $enBase['familles'] ?? 0,
            $sansFamille,
        ));

        return Command::SUCCESS;
    }

    /**
     * Aucune colonne n'est réécrite sans condition : le bloc `profession` est
     * présent dans les 3 117 fiches mais vide dans 962 d'entre elles, sans
     * qu'aucun indicateur ne dise si le député n'en a jamais déclaré ou si la
     * moisson ne l'a pas reprise. Le régime `$misAJourSiRenseigne` tranche dans
     * le sens de la conservation, comme le fait l'application d'origine, qui
     * n'efface jamais un profil déjà constitué.
     *
     * @param list<list<mixed>> $lot
     */
    private function enregistre(array $lot): void
    {
        $this->upsert('profil_social', self::COLONNES, $lot, [], \array_slice(self::COLONNES, 1));
    }

    /** Une chaîne vide de l'open data vaut absence de donnée, pas donnée vide. */
    private function texte(?string $valeur): ?string
    {
        $valeur = trim((string) $valeur);

        return $valeur === '' ? null : $valeur;
    }
}
