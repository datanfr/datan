-- app:import:elections — catalogue des élections, tenu par la rédaction.
-- Le NULLIF sur dateSecondRound n'est pas un ornement : les européennes sont à
-- tour unique et la base y écrit 0000-00-00, que Doctrine refuse de relire.
SELECT id, slug, libelle, libelleAbrev, dateYear, dateFirstRound,
       NULLIF(dateSecondRound, '0000-00-00') AS dateSecondRound,
       candidates, resultsUrl
FROM elect_libelle
ORDER BY id;
