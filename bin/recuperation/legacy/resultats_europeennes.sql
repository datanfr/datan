-- app:import:resultats-electoraux — européennes, par commune (2,5 M lignes).
SELECT year, dpt, commune, party, value
FROM elect_europe_results
ORDER BY dpt, commune, year;
