-- datan:sans-en-tete
-- app:import:vote-groupes --file=… — ventilation du groupe MODEM (PO730970)
-- sur les sept scrutins de la 15e dont l'open data a perdu le bloc (7 lignes).
-- Format sans en-tête attendu par ImportVoteGroupesCommand.
SELECT voteId, organeRef, nombreMembresGroupe, positionMajoritaire, nombrePours,
       nombreContres, nombreAbstentions, IFNULL(nonVotants, 0), IFNULL(nonVotantsVolontaires, 0)
FROM votes_groupes
WHERE legislature = 15
  AND voteNumero IN (2418, 2744, 2751, 2752, 2761, 2762, 2766)
  AND organeRef = 'PO730970';
