-- app:import:amendements-clos — amendements mis aux voix des législatures closes.
--
-- Aucun dépôt des Tricoteuses ne les porte (seul Amendements_XVII est
-- moissonné) : le legacy les tient de ses archives XML de l'open data
-- (Amendements_XV.xml.zip…), et son rattachement scrutin → amendement d'un
-- scraping de assemblee-nationale.fr (`votes_amendments`). Sans eux, les pages
-- de vote des 15e et 16e législatures perdent l'exposé des motifs, l'auteur
-- de l'amendement et le résumé de la rédaction (`exposes`).
--
-- La 17e est exclue : le sync quotidien la tient à jour.
SELECT va.legislature, va.voteNumero, a.id AS amendement_id, va.amendmentHref,
       a.expose, a.numOrdre, a.seanceRef, a.sortDate,
       aa.type AS auteur_type, aa.acteurRef AS auteur_ref
FROM votes_amendments va
JOIN amendements a ON a.id = va.amendmentId
LEFT JOIN amendements_auteurs aa ON aa.id = a.id
WHERE va.legislature < 17
ORDER BY va.legislature, va.voteNumero;
