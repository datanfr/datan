-- app:import:circonscriptions — participation aux législatives, par circonscription.
SELECT dpt, circo, tour, inscrits, abstentions, votants, blancs, nuls, exprimes, year
FROM elect_legislatives_infos;
