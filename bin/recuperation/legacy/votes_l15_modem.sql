-- datan:sans-en-tete
-- app:import:votes --file=… (jamais --truncate) — votes nominatifs de sept
-- scrutins de la 15e que l'open data republie sans le bloc du groupe MODEM
-- (PO730970) : 234 votes que le legacy, moissonné à l'époque, a gardés. On
-- reprend tout le décompte nominatif de ces sept scrutins (3 019 lignes) :
-- l'upsert réécrit à l'identique ce que le sync a déjà.
-- Format sans en-tête attendu par ImportVotesCommand ; `-N -B` écrirait NULL en
-- toutes lettres, d'où les IFNULL.
SELECT mpId, voteId, vote, voteType, IFNULL(causePosition, ''), IFNULL(parDelegation, 'false')
FROM votes
WHERE legislature = 15
  AND voteNumero IN (2418, 2744, 2751, 2752, 2761, 2762, 2766)
  AND voteType = 'decompteNominatif';
