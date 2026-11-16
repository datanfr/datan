-- app:import:decryptages — décryptages éditoriaux, brouillons compris.
-- `vote_id` est l'uid du scrutin (VTANR… / VTCGR…) : c'est par lui que l'import
-- retrouve un vote du Congrès, dont `voteNumero` vaut -1. `created_by` et
-- `modified_by` sont des `users.id`, traduits par `utilisateur.id_origine`.
SELECT id, legislature, voteNumero, vote_id, title, slug, category, reading,
       description, state, created_at, modified_at, created_by, modified_by
FROM votes_datan
ORDER BY id;
