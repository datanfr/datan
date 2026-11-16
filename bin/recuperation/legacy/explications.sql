-- app:import:explications — explications de vote des députés. Un vote du
-- Congrès y porte le numéro sentinelle -1 : l'import le résout par le décryptage.
SELECT mpId, legislature, voteNumero, text, state, created_at, modified_at
FROM explications_mp
ORDER BY id;
