-- app:import:quiz — questionnaire, chaque question adossée à un scrutin.
SELECT q.id, q.quizz, q.voteNumero, q.legislature, q.title, q.explication,
       q.for1, q.for2, q.for3, q.against1, q.against2, q.against3,
       f.slug AS category_slug, f.name AS category_name, q.swap, q.state,
       q.created_at, q.modified_at
FROM quizz q
LEFT JOIN fields f ON f.id = q.category
ORDER BY q.id;
