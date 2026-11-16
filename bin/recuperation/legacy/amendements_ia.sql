-- app:import:resumes-amendements — résumés IA des amendements de la 17e
-- (génération déjà payée, qu'on ne refait pas). Plusieurs lignes par amendement
-- sont possibles : l'import garde la plus récente qui porte un résumé, d'où le
-- tri par id. `anthropic_batches` et `anthropic_batch_items` ne se reprennent
-- pas : le suivi des lots repart de zéro au port.
SELECT id, amendementId, titre_ia, resume_ia, simplicite_ia, justification_ia, categorie, reviewed
FROM amendements_ia
ORDER BY id;
