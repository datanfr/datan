<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les dossiers législatifs depuis le dépôt des Tricoteuses.
 *
 * Le code de procédure parlementaire est ce qui distingue un texte du
 * Gouvernement (projet de loi) d'un texte parlementaire (proposition de loi) :
 * c'est lui qui fonde le taux de soutien au gouvernement affiché sur les pages
 * de groupe.
 *
 * La commission saisie au fond, elle, fonde le score « Votes par
 * spécialisation » : elle se lit dans l'arbre des actes législatifs du dossier,
 * sur l'acte `AN1-COM-FOND` (1 662 dossiers de la 17e le portent, couvrant
 * 7 275 scrutins sur 8 434).
 *
 * @see \App\Entity\Dossier::PROCEDURES_GOUVERNEMENT
 */
#[AsCommand(
    name: 'app:import:dossiers',
    description: 'Importe les dossiers législatifs depuis le dépôt des Tricoteuses.',
)]
class ImportDossiersCommand extends ImportTricoteusesCommand
{
    private const COLONNES = [
        'dossier_id', 'legislature', 'titre', 'titre_chemin',
        'procedure_parlementaire', 'procedure_code', 'commission_fond',
    ];

    /**
     * Colonnes réécrites sans condition en cas de doublon : tout sauf la clé et
     * `commission_fond`, qui ne se réécrit que renseignée — un dossier remanié
     * dont l'acte disparaîtrait d'une moisson n'invalide pas la commission déjà
     * connue (règle des imports : jamais écraser avec du vide).
     */
    private const COLONNES_MAJ = [
        'legislature', 'titre', 'titre_chemin',
        'procedure_parlementaire', 'procedure_code',
    ];

    /** Code de l'étape « Travaux de la commission saisie au fond ». */
    private const ACTE_COMMISSION_FOND = 'AN1-COM-FOND';

    protected function depotParDefaut(): string
    {
        return 'dossiers';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tout = (bool) $input->getOption('tout');
        $chemin = $this->chemin($input);

        $io->title('Import des dossiers législatifs');
        $io->text($this->incremental($chemin, $tout) ? 'Régime incrémental : seuls les dossiers modifiés.' : 'Import complet du dépôt.');

        $lus = 0;
        $lot = [];

        foreach ($this->fichiers($chemin, 'dossiers', $tout) as $fichier) {
            $dossier = $this->lisJson($fichier);
            if ($dossier === null || !isset($dossier['uid'])) {
                continue;
            }
            ++$lus;

            $lot[] = [
                $dossier['uid'],
                $this->entier($dossier['legislature'] ?? null),
                $dossier['titreDossier']['titre'] ?? null,
                $dossier['titreDossier']['titreChemin'] ?? null,
                $dossier['procedureParlementaire']['libelle'] ?? null,
                $this->entier($dossier['procedureParlementaire']['code'] ?? null),
                $this->commissionFond($dossier['actesLegislatifs'] ?? []),
            ];

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('dossier', self::COLONNES, $lot, self::COLONNES_MAJ, ['commission_fond']);
                $lot = [];
            }
        }

        $this->upsert('dossier', self::COLONNES, $lot, self::COLONNES_MAJ, ['commission_fond']);

        $io->success(sprintf(
            '%d dossier%s traité%s — %d en base, dont %d avec commission au fond.',
            $lus,
            $lus > 1 ? 's' : '',
            $lus > 1 ? 's' : '',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM dossier'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM dossier WHERE commission_fond IS NOT NULL'),
        ));

        return Command::SUCCESS;
    }

    /**
     * Organe de la commission saisie au fond : l'`organeRef` du premier acte
     * `AN1-COM-FOND` rencontré en parcourant l'arbre des actes en profondeur.
     *
     * « Premier » n'est pas un raccourci : un texte navette porte un acte par
     * lecture à l'Assemblée, et le legacy prend le premier de l'ordre du
     * document (`daily.php:2834`, XPath `[0]`) — c'est la commission de la
     * première lecture qui fait foi, et le parcours en profondeur reproduit
     * exactement cet ordre.
     *
     * @param list<mixed> $actes
     */
    private function commissionFond(array $actes): ?string
    {
        foreach ($actes as $acte) {
            if (!\is_array($acte)) {
                continue;
            }

            // L'organe est exigé avec le code : le XPath d'origine se termine
            // par `/organeRef` et saute donc un acte qui n'en porterait pas.
            if (($acte['codeActe'] ?? null) === self::ACTE_COMMISSION_FOND && isset($acte['organeRef'])) {
                return (string) $acte['organeRef'];
            }

            $organe = $this->commissionFond($acte['actesLegislatifs'] ?? []);
            if ($organe !== null) {
                return $organe;
            }
        }

        return null;
    }
}
