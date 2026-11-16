-- app:import:reseaux-sociaux — coordonnées et réseaux sociaux des députés.
-- `tiktok` et `instagram` ne sont pas exportés : `contact_depute` n'a pas ces
-- colonnes et aucune page du legacy ne les affiche (une valeur chacune au
-- 16 novembre 2026).
SELECT mpId, website, mailAn, mailPerso, twitter, facebook, bluesky, dateMaj
FROM deputes_contacts
ORDER BY mpId;
