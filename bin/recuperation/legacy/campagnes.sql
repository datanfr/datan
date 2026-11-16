-- app:import:campagnes — bandeaux d'appel aux dons. `author` est un `users.id`,
-- résolu ici en nom affiché : c'est ce que porte `campagne.auteur`.
SELECT c.id, c.text, c.start_date, c.end_date, COALESCE(u.name, c.author) AS auteur,
       c.position, c.page, c.is_active, c.created_at, c.updated_at
FROM campaigns c
LEFT JOIN users u ON u.id = c.author
ORDER BY c.id;
