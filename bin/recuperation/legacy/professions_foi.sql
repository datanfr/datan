-- app:import:professions-foi — index des professions de foi. Les PDF sont des
-- fichiers (`assets/data/professions/` du legacy), à copier à part : seule
-- cette table sait lesquels existent.
SELECT id, mpId, electionId, file, tour
FROM profession_foi
ORDER BY id;
