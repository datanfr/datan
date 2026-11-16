-- app:import:circonscriptions — résultats des législatives, par circonscription.
-- Les noms y sont doublement encodés en UTF-8 : l'import les répare.
SELECT year, dpt, circo, tour, nuance, candidat, nameLast, nameFirst, sexe,
       voix, pct_inscrits, pct_exprimes, elected
FROM elect_legislatives_results;
