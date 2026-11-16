-- app:import:newsletter — abonnés. `id_user` est un `users.id` du legacy, que
-- l'import traduit par `utilisateur.id_origine`.
-- À exporter le plus tard possible : le legacy inscrit jusqu'à la bascule.
SELECT email, general, votes, depute, departement, id_user, created_at, updated_at
FROM newsletter
ORDER BY id;
