-- app:import:communes — communes limitrophes, restreintes à celles qui ont une
-- circonscription (les autres n'ont pas de page).
SELECT DISTINCT a.insee, a.adjacente
FROM cities_adjacentes a
JOIN circos c ON c.insee = a.adjacente
ORDER BY a.insee, a.adjacente;
