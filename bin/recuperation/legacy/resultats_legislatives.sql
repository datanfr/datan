-- app:import:resultats-electoraux — législatives, par commune.
SELECT year, tour, dpt, commune, circo, insee, candidate,
       inscrits, abs, votants, blancs, nuls, exprimes,
       nom, prenom, sexe, nuance, voix
FROM elect_legislatives_cities
ORDER BY insee, year, tour, CAST(candidate AS UNSIGNED);
