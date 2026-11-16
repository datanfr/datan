-- app:import:exposes — résumés de l'exposé des motifs, rédigés pour les
-- scrutins sur amendement des législatures closes (16e en novembre 2026) et
-- affichés sous « Résumé de l'amendement ». `exposeSummary` est le brouillon
-- (généré), `exposeSummaryPublished` la version relue ; « NA » y marque un
-- résumé refusé par la rédaction. La 17e passe par `amendements_ia`.
SELECT legislature, voteNumero, exposeSummary, exposeSummaryPublished, dateMaj
FROM exposes
ORDER BY id;
