-- app:import:utilisateurs — comptes de connexion, tous types confondus
-- (admin, writer, mp, et lecteurs au type vide).
-- Le mot de passe est le hachage bcrypt du legacy, repris tel quel.
-- `users_mp` porte le rattachement d'un compte « mp » à son député.
SELECT u.id, u.username, u.name, u.email, u.type, u.password, u.registration_date, u.zipcode, um.mpId
FROM users u
LEFT JOIN users_mp um ON um.user = u.id
ORDER BY u.id;
