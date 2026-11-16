-- app:import:articles — articles du blog, brouillons compris. L'auteur est
-- résolu en texte : un article garde sa signature même si le compte a disparu.
SELECT p.category_id, p.title, p.slug, p.body, p.state, p.image_name,
       p.created_at, p.modified_at, u.name AS auteur
FROM posts p
LEFT JOIN users u ON u.id = p.user_id
ORDER BY p.id;
