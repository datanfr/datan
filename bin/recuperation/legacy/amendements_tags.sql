-- app:import:resumes-amendements — mots-clés des amendements de la 17e,
-- normalisés à l'import (NormaliseurTag), sans appel au modèle.
SELECT amendementId, tag
FROM amendements_tags
ORDER BY id;
