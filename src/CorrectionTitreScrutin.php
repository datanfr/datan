<?php

namespace App;

/**
 * Corrections manuelles des titres de scrutins, reprises telles quelles de
 * correct_votes_title() (scripts/daily.php:219 de l'application d'origine).
 *
 * L'open data publie des titres fautifs — coquilles (« fin des gestion »,
 * « ascenceur »), intitulés périmés après renommage du texte, mentions
 * « , adoptée par le Sénat, » que le site retire. La rédaction les corrige à
 * la main depuis toujours ; ces titres s'affichent partout (pages de vote,
 * listes, décryptages) et la parité avec datan.fr en dépend.
 *
 * L'ORDRE DES ENTRÉES COMPTE : str_replace() les applique en séquence, et
 * certaines s'enchaînent — « entre 1942 et 1982 » se corrige d'abord en
 * « entre 1945 et 1982 », que des entrées suivantes reprennent dans des
 * phrases entières. Ne pas trier.
 */
final class CorrectionTitreScrutin
{
    private const CORRECTIONS = [
        "projet de loi de finances de fin des gestion pour 2024" => "projet de loi de finances de fin de gestion pour 2024",
        "proposition de loi, adoptée par le Sénat, relative" => "proposition de loi relative",
        "lutter contre les pannes d'ascenceur non prises en charge" => "lutter contre les pannes d'ascenseurs non prises en charge",
        "proposition de résolution européenne visant à refuser la ratification de l'accord commercial entre l'Union européenne et le Mercosur" => "proposition de résolution européenne invitant le Gouvernement de la République française à refuser la ratification de l'accord commercial entre l'Union européenne et le Mercosur",
        ", adoptée par le Sénat," => "",
        "proposition de loi relative à la sûreté dans les transports" => "proposition de loi relative au renforcement de la sûreté dans les transports",
        "projet de loi portant diverses dispositions d'adaptation au droit de l'Union européenne en matière économique, financière, environnementale, énergétique, de santé et de circulation des personnes" => "projet de loi portant diverses dispositions d'adaptation au droit de l'Union européenne en matière économique, financière, environnementale, énergétique, de transport, de santé et de circulation des personnes",
        "proposition de loi relative à l'organisation et aux missions des personnels de santé professionnels et volontaires des services d'incendie et de secours" => "proposition de loi portant création du cadre d'emploi des personnels de santé des services d’incendie et de secours",
        "endiguer le prolifération" => "endiguer la prolifération",
        "indivison" => "indivision",
        "proposition de loi créant une dérogation à la participation minimale pour la maîtrise d'ouvrage  pour les communes rurales" => "proposition de loi créant une dérogation à la participation minimale pour la maîtrise d'ouvrage pour les communes rurales",
        "proposition de loi visant à lutter contre la disparition des terres agricoles et renforcer la régulation des prix du foncier agricole" => "proposition de loi visant à lutter contre la disparition des terres agricoles et à renforcer la régulation des prix du foncier agricole",
        "proposition de loi organique fixant le statut du procureur national anti-stupéfiants" => "proposition de loi organique fixant le statut du procureur de la République anti-criminalité organisée",
        "réformer le mode d'élection du Conseil de Paris et des conseils municipaux de Lyon et Marseille" => "réformer le mode d'élection des membres du Conseil de Paris et des conseils municipaux de Lyon et Marseille",
        "de Londres de 1966" => "de Londres de 1996",
        "proposition de résolution visant à étendre les compétences du Parquet européen aux infractions à l’environnement." => "proposition de résolution européenne visant à étendre les compétences du Parquet européen aux infractions à l’environnement",
        "proposition de loi visant à faciliter la transformation des bureaux et autres bâtiments en logements" => "proposition de loi visant à faciliter la transformation des bureaux en logements",
        "proposition de loi relative à l'accompagnement et aux soins palliatifs" => "proposition de loi visant à garantir l'égal accès de tous à l'accompagnement et aux soins palliatifs",
        "ssimplification" => "simplification",
        "proposition de loi portant programmation nationale énergie et climat pour les années 2025 à 2035" => "proposition de loi portant programmation nationale et simplification normative dans le secteur économique de l’énergie",
        "proposition de loi autorisant la ratification du Traité de coopération en matière de défense entre la République française et la République de Djibouti" => "projet de loi autorisant la ratification du Traité de coopération en matière de défense entre la République française et la République de Djibouti",
        "proposition de loi portant plusieurs mesures pour limiter les frais bancaires" => "proposition de loi portant plusieurs mesures de justice pour limiter les frais bancaires",
        "proposition de résolution européenne appelant à la régulation des réseaux sociaux face aux ingérences étrangères." => "proposition de résolution rappelant l’urgence démocratique d’appliquer pleinement et entièrement le règlement européen sur les services numériques",
        "proposition de loi visant à réformer le mode d’élection des membres du conseil de Paris et des conseils municipaux de Lyon et de Marseille" => "proposition de loi visant à réformer le mode d’élection des membres du conseil de Paris et des conseils municipaux de Lyon et Marseille",
        "projet de loi portant création de l'établissement public du commerce et de l'industrie de Corse" => "projet de loi portant création de l'établissement public du commerce et de l'industrie de la collectivité de Corse",
        "projet de loi de fin de gestion pour 2025" => "projet de loi de finances de fin de gestion pour 2025",
        "mise en oeuvre du Protocole de" => "mise en œuvre du Protocole de",
        "interdire le démarchage des titulaires" => "interdire le démarchage de ses titulaires",
        "fonctionnement du marché de travail en vue du plein emploi" => "fonctionnement du marché du travail en vue du plein emploi",
        "projet de loi de programmation des finances publiques pour les années 2023-2027" => "projet de loi de programmation des finances publiques pour les années 2023 à 2027",
        "revivifier la représentation nationale" => "revivifier la représentation politique",
        "projet de loi, adopté par le Sénat, autorisant l’approbation de l’accord pour la mise en place d’un mécanisme" => "projet de loi autorisant l’approbation de l’accord pour la mise en place d’un mécanisme",
        "proposition de loi visant à garantir un tarif réduit aux étudiants boursiers et précaires dans les sites de restauration gérés par les centres régionaux des oeuvres universitaires" => "proposition de loi visant à assurer un repas à 1 euro pour tous les étudiants",
        "inégibilité" => "inéligibilité",
        "pour favoriser les travaux de la rénovation énergétique" => "pour favoriser les travaux de rénovation énergétique",
        "proposition de résolution tendant à la création d'une commission d'enquête sur la structuration, le financement, les moyens et les modalités d'action des groupuscules auteurs de violences à l'occasion de manifestations et rassemblements intervenus entre le 16 mars et le 3 mai 2023, ainsi que sur le déroulement de ces manifestations et rassemblements" => "proposition de résolution, tendant à la création d’une commission d’enquête sur la structuration, le financement, l’organisation des groupuscules et la conduite des manifestations illicites violentes entre le 16 mars 2023 et le 4 avril 2023",
        "visant à renforcer la continuité territoriale en Outre-Mer" => "visant à renforcer le principe de la continuité territoriale en Outre-Mer",
        "objet de spoliation dans le contexte" => "objet de spoliations dans le contexte",
        "relatif à l'ouverture, la modernisation et la responsabilité du corps judiciaire" => "relatif à l'ouverture, à la modernisation et à la responsabilité du corps judiciaire",
        "adapter les dispositions du code du commerce relatives" => "adapter les dispositions du code de commerce relatives",
        "titres-restaurant pour les achats" => "titres-restaurant pour des achats",
        "proposition de résolution tendant à la création d'une commission d'enquête sur la gestion des risques naturels majeurs dans les territoires d'outre-mer" => "proposition de résolution tendant à la création d’une commission d’enquête sur la gestion par l’État des risques naturels majeurs dans les territoires transocéaniques de France, dits d’Outre-mer",
        "entre 1942 et 1982" => "entre 1945 et 1982",
        "proposition de loi portant réparation des préjudices subis par les personnes condamnées pour homosexualité entre 1945 et 1982" => "proposition de loi portant réparation des personnes condamnées pour homosexualité entre 1945 et 1982",
        "proposition de loi portant reconnaissance de la Nation et réparation des préjudices subis par les personnes condamnées pour homosexualité entre 1945 et 1982" => "proposition de loi portant réparation des personnes condamnées pour homosexualité entre 1945 et 1982",
        "proposition européenne relative à l'adoption d'une loi européenne sur l'espace" => "proposition de résolution européenne relative à l’adoption d’une loi européenne sur l’espace",
        "proposition de loi visant à la prise en charge par l'État de l'accompagnement humain des élèves en situation de handicap durant le temps de pause méridienne" => "proposition de loi visant la prise en charge par l’État de l’accompagnement humain des élèves en situation de handicap sur le temps méridien",
        "proposition de loi visant à la prise en charge par l'État de l'accompagnement humain des élèves en situation de handicap pendant le temps de pause méridienne" => "proposition de loi visant la prise en charge par l’État de l’accompagnement humain des élèves en situation de handicap sur le temps méridien",
        "proposition de loi visant à protéger la population des risques liés aux per- et polyfluoroalkylées" => "proposition de loi visant à protéger la population des risques liés aux substances per- et polyfluoroalkylées"
    ];

    public static function corriger(?string $titre): ?string
    {
        if ($titre === null) {
            return null;
        }

        // Le nettoyage « n? » → « n° » vient du même endroit du legacy
        // (updateVoteInfo, daily.php:1744) : l'open data livre parfois le
        // signe degré cassé.
        return str_replace(
            array_merge(array_keys(self::CORRECTIONS), ['n?']),
            array_merge(array_values(self::CORRECTIONS), ['n°']),
            $titre,
        );
    }
}
