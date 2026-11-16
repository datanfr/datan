-- app:opendata:publier — scores des députés que `classement` ne porte pas.
--
-- Les jeux CSV publiés sur data.gouv (`opendata_*` de daily.php) donnent quatre
-- scores par député et par législature. Nos classements ne les calculent que
-- pour les députés en exercice de la législature courante ; pour les autres —
-- législatures closes, députés partis en cours de 17e —, les valeurs du legacy
-- ne bougeront plus et sont reprises telles quelles, avec leurs définitions
-- (participation à TOUS les scrutins, deux décimales ; loyauté et majorité,
-- trois). Sortie : ressources/opendata/gel-legacy-deputes.tsv, versionnée.
--
-- La majorité de la 17e est écartée : sans groupe « Majoritaire » depuis 2024,
-- le legacy y écrit 0.000 pour tous, ce qui n'est pas une mesure (CLAUDE.md).
SELECT da.mpId, da.legislature,
       cp.score AS participation,
       cpc.score AS participationSpecialite,
       cl.score AS loyaute,
       CASE WHEN da.legislature >= 17 THEN NULL ELSE cm.score END AS majorite
FROM deputes_last da
LEFT JOIN class_participation cp ON da.mpId = cp.mpId AND da.legislature = cp.legislature
LEFT JOIN class_participation_commission cpc ON da.mpId = cpc.mpId AND da.legislature = cpc.legislature
LEFT JOIN class_loyaute cl ON da.mpId = cl.mpId AND da.legislature = cl.legislature
LEFT JOIN class_majorite cm ON da.mpId = cm.mpId AND da.legislature = cm.legislature
WHERE da.legislature < 17 OR NOT da.active
ORDER BY da.mpId;
