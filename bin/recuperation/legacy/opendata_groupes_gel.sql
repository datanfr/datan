-- app:opendata:publier — statistiques des groupes dissous.
--
-- Pendant de opendata_deputes_gel.sql pour `groupes-historique.csv` : un groupe
-- dissous ne vote plus, ses chiffres sont ceux du jour de sa disparition. Nos
-- classements ne portant que les groupes en activité, ceux du legacy sont
-- repris tels quels. Sortie : ressources/opendata/gel-legacy-groupes.tsv.
SELECT o.uid,
       ge.effectif,
       gs.womenPct AS women,
       gs.age,
       gs.rose_index AS rose,
       cohesion.value AS cohesion,
       participation.value AS participation,
       majority.value AS majorite
FROM organes o
LEFT JOIN groupes_stats gs ON gs.organeRef = o.uid
LEFT JOIN groupes_effectif ge ON ge.organeRef = o.uid
LEFT JOIN class_groups cohesion ON cohesion.organeRef = o.uid AND cohesion.stat = "cohesion"
LEFT JOIN class_groups participation ON participation.organeRef = o.uid AND participation.stat = "participation"
LEFT JOIN class_groups majority ON majority.organeRef = o.uid AND majority.stat = "majority"
WHERE o.coteType = "GP" AND o.legislature >= 14 AND o.dateFin IS NOT NULL
ORDER BY o.uid;
