-- app:import:faq — questions de la FAQ, brouillons compris.
SELECT id, title, slug, text, category, state, created_at, modified_at
FROM faq_posts
ORDER BY id;
