<?php

namespace App;

/**
 * Libellé éditorial du « Type de vote » affiché dans l'encart Infos d'une page
 * de scrutin, et son explication en info-bulle.
 *
 * Porté de `Votes_model::get_individual_vote()` (le bloc `type_edited` /
 * `type_edited_explication`, ~120 lignes de `if`). Ce n'est PAS le type de
 * scrutin de l'Assemblée (`code_type_vote` : SPO, SPS…), que la page n'affiche
 * jamais : c'est une lecture de la rédaction, tirée de la nature du vote
 * (`scrutin.nature_vote`) et, pour un vote final, de la procédure du dossier.
 *
 * Les textes sont repris mot pour mot, liens compris — ils sont rendus en HTML
 * dans l'info-bulle, comme sur datan.fr.
 */
final class TypeVoteEdito
{
    /**
     * Votes finaux : la procédure parlementaire du dossier fait le libellé.
     * Clé = `dossier.procedure_parlementaire`.
     */
    private const PAR_PROCEDURE = [
        'Projet de loi ordinaire' => [
            'projet de loi',
            'Un projet de loi est un texte destiné à devenir une loi et qui émane du Gouvernement, contrairement à la proposition de loi, qui émane des députés.',
        ],
        'Proposition de loi ordinaire' => [
            'proposition de loi',
            "Une proposition de loi est un texte destiné à devenir une loi et qui émane d'un député ou d'un sénateur, contrairement au projet de loi qui émane du Gouvernement.",
        ],
        'Projet ou proposition de loi constitutionnelle' => [
            'projet ou proposition de loi constitutionnelle',
            "Un projet ou proposition de loi constitutionnelle est une loi visant à modifier la Constitution. Soit cette modification est à l'origine du Gouvernement (projet de loi) soit du Parlement (proposition de loi).<br><br><a href='https://www2.assemblee-nationale.fr/decouvrir-l-assemblee/role-et-pouvoirs-de-l-assemblee-nationale/les-fonctions-de-l-assemblee-nationale/les-fonctions-legislatives/la-revision-de-la-constitution' target='_blank'>Plus d'information sur la procédure de révision de la Constitution</a>",
        ],
        'Projet ou proposition de loi organique' => [
            'Projet ou proposition de loi organique',
            "Un projet ou proposition de loi organique est une loi complétant la Constitution afin de préciser l'organisation des pouvoirs publics. Elle est hiérarchiquement en dessous de la Constitution, mais au dessus des lois ordinaires.",
        ],
        'Projet de loi de finances rectificative' => [
            'Projet de loi de finances rectificative',
            "Un projet de loi de finances rectificative a pour but de corriger le budget initial afin de tenir compte de l'évolution de la conjecture économique et financière du pays.",
        ],
        'Projet de ratification des traités et conventions' => [
            'Ratification de traités et conventions',
            "Alors que les traités et conventions internationales sont négociés et ratifiés en général par le Président de la République, certains accords internationaux nécessitent l'accord du Parlement.",
        ],
        'Projet de loi de financement de la sécurité sociale' => [
            'Projet de loi de financement de la sécurité sociale',
            "Le Projet de loi de financement de la sécurité sociale (PLFSS) vise à gérer les dépenses sociales et de santé. Le projet, présenté à l'automne par le gouvernement, fixe les objectifs de dépenses en fonction des recettes, et détermine donc les conditions à l'équilibre financier de la Sécurité sociale.",
        ],
        "Projet de loi de finances de l'année" => [
            'Projet de loi de finances',
            "Le projet de loi de finances (PLF), présenté à l'automne par le Gouvernement, est le projet de budget pour la France. C'est un document unique rassemblant l'ensemble des recettes et des dépenses de l’État pour l'année à venir. Le projet propose le montant, la nature et l'affectation des ressources et des charges de l’État. L'Assemblée nationale vote ici sur une partie de ce projet de loi de finances.<br><br><a href='https://www.gouvernement.fr/projet-de-loi-de-finances-plf-qu-est-ce-que-c-est' target='_blank'>Plus d'information</a>",
        ],
        'Résolution' => [
            'proposition de résolution',
            "Une proposition de résolution est un texte non législatif (qui est donc purement symbolique) et qui sert à exprimer la position de l'Assemblée nationale sur un sujet donné.",
        ],
    ];

    /**
     * Autres natures de vote (`scrutin.nature_vote`, porté par {@see NatureVote}).
     * Une explication nulle vaut libellé sans info-bulle, comme le legacy.
     */
    private const PAR_NATURE = [
        'amendement' => [
            'amendement',
            "Un amendement est une modification apportée à un texte juridique, d'un projet de loi par exemple.",
        ],
        'sous-amendement' => [
            'sous-amendement',
            "Un sous-amendement est, comme un amendement, une modification apportée à un texte juridique, d'un projet de loi par exemple. Le sous-amendement porte sur un amendement, et ne peut pas contredire le sens initial de l'amendement.",
        ],
        'article' => [
            'article',
            "Un article est une partie d'un texte juridique, d'un projet de loi par exemple.",
        ],
        'declaration de politique generale' => [
            'déclaration de politique générale',
            "La déclaration de politique générale est une déclaration du Premier ministre devant l'Assemblée nationale lors de l'entrée en fonction d'un nouveau gouvernement.",
        ],
        'motion de censure' => [
            'motion de censure',
            "Une motion de censure est le moyen dont dispose le Parlement pour montrer sa désapprobation envers la politique du Gouvernement. Pour être recevable, la motion doit être signée par au moins un dixième des membres de l'Assemblée nationale. Le vote doit avoir lieu au maximum 48 heures après le dépôt de la motion. Pour être adoptée, la motion doit obtenir la majorité absolue, autrement dit la moitié des membres composants l'Assemblée.<br><br><a href='http://www2.assemblee-nationale.fr/dans-l-hemicycle/engagements-de-responsabilite-du-gouvernement-et-motions-de-censures#node_23387' target='_blank'>Plus d'information</a>",
        ],
        'motion de rejet préalable' => [
            'motion de rejet préalable',
            "La motion de rejet préalable est discutée et votée avant la discussion générale d'un texte de loi. Elle vise à indiquer que le texte (projet ou proposition de loi) est contraire à une ou plusieurs dispositions constitutionnelles. Si la motion est adoptée, elle entraîne le rejet du texte.<br><br><a href='http://www.assemblee-nationale.fr/encyclopedie/loi.asp' target='_blank'>Plus d'information</a>",
        ],
        'motion de renvoi en commission' => [
            'motion de renvoi en commission',
            "Si une motion de renvoi en commission est adoptée, les débats sur le texte sont suspendus jusqu'à la présentation par la commission principale d'un nouveau rapport.",
        ],
        "motion d'ajournement" => [
            "motion d'ajournement",
            "Une motion d'ajournement est utilisé que dans le cadre des accords internationaux. Si la motion est adoptée, le texte retourne en commission parlementaire, où il doit être rediscuté.<br><br><a href='https://www.lemonde.fr/blog/cuisines-assemblee/2019/02/18/la-motion-dajournement/' target='_blank'>Plus d'information</a>",
        ],
        'conclusions de rejet de la commission' => [
            'conclusions de rejet de la commission',
            "Quand un député propose une loi, une commission parlementaire est en charge de la discussion et de rédiger un rapport. Elle peut conclure au rejet de la proposition. Si cette proposition de rejet est adoptée, alors la proposition de loi est directement rejetée, avant discussion.",
        ],
        'crédits de mission' => [
            'crédits de mission',
            "Les crédits de mission sont adoptés dans les lois de finance. Ces dispositions fixent les crédits pour les différentes missions de l'État.",
        ],
        'déclaration du gouvernement' => [
            'déclaration du gouvernement',
            "La déclaration du gouvernement peut, de sa propre initiative ou à la demande d'un groupe parlementaire, faire une déclaration qui donne lieu à un débat sur un sujet déterminé. Si le gouvernement le souhaite, ce débat peut faire l'objet d'un vote.<br><br>Les modalités de ces déclarations sont prévues à <a href='http://www.assemblee-nationale.fr/connaissance/constitution.asp' target='_blank'>l'article 51-1 de la Constitution</a>.",
        ],
        'demande de constitution de commission speciale' => [
            'demande de constitution de commission spéciale',
            "Une commission spéciale est une commission chargée spécifiquement de l'examination d'un projet de loi. Une commission spéciale peut être proposée par le Gouvernement ou par l'Assemblée nationale. Lorsque le Gouvernement, le président d'une commission permanente ou le président d'un groupe s'y oppose, cette demane fait l'objet d'un vote.<br><br><a href='http://www2.assemblee-nationale.fr/14/autres-commissions/commissions-speciales/liens/constitution-d-une-commission-speciale' target='_blank'>Plus d'informations<a/>",
        ],
        'partie du projet de loi de finances' => [
            'partie du projet de loi de finances',
            "Le projet de loi de finances (PLF), présenté à l'automne par le Gouvernement, est le projet de budget pour la France. C'est un document unique rassemblant l'ensemble des recettes et des dépenses de l’État pour l'année à venir. Le projet propose le montant, la nature et l'affectation des ressources et des charges de l’État. L'Assemblée nationale vote ici sur une partie de ce projet de loi de finances.<br><br><a href='https://www.gouvernement.fr/projet-de-loi-de-finances-plf-qu-est-ce-que-c-est' target='_blank'>Plus d'information</a>",
        ],
        'projet de loi constitutionnelle' => [
            'projet de loi constitutionnelle',
            'Un projet de loi constitutionnelle est une proposition visant à modifier la Constitution, c\'est à dire la loi fondamentale du pays. Un projet de loi constitutionnelle doit être adoptée à l\'identique par les deux chambres (Assemblée nationale et Sénat), réunies pour l\'occasion en Congrès. ',
        ],
    ];

    /**
     * @return array{libelle: string, explication: string|null}|null null quand
     *   la nature du vote est inconnue — l'encart tait alors la ligne
     */
    public static function pour(?string $natureVote, ?string $procedure): ?array
    {
        if ($natureVote === null) {
            return null;
        }

        // Un vote final se nomme par la procédure de son dossier ; sans dossier
        // rattaché, le legacy ne dit rien plutôt que de dire « final ».
        if ($natureVote === NatureVote::FINALE) {
            $entree = $procedure !== null ? (self::PAR_PROCEDURE[$procedure] ?? null) : null;

            return $entree === null ? null : ['libelle' => $entree[0], 'explication' => $entree[1]];
        }

        $entree = self::PAR_NATURE[$natureVote] ?? null;

        // Nature connue de nous mais absente de la table : le legacy affiche
        // alors le libellé brut, sans info-bulle.
        return $entree === null
            ? ['libelle' => $natureVote, 'explication' => null]
            : ['libelle' => $entree[0], 'explication' => $entree[1]];
    }
}
