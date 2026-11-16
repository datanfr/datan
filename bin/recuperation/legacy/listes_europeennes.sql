-- app:import:resultats-electoraux — listes des européennes.
SELECT id_liste, year, name, tete, parti
FROM elect_europe_listes
ORDER BY year, CAST(id_liste AS UNSIGNED);
