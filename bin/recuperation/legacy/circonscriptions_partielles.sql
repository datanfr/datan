-- app:import:circonscriptions — législatives partielles.
SELECT year, dpt, circo, tour, date, nuance, nameLast, nameFirst, sexe,
       voix, pct_exprimes, elected
FROM elect_legislatives_partielles;
