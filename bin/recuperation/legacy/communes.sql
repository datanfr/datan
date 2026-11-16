-- app:import:communes — communes et circonscriptions qui les découpent.
--
-- Les NULLIF(…, '0') ne sont pas décoratifs : dans une copie de la base, les
-- deux communes nommées « Faux » (08165 dans les Ardennes, 24177 en Dordogne)
-- portaient commune_nom = '0' et commune_slug = '0' — « Faux » est le libellé
-- français du booléen FALSE, un aller-retour par un tableur les a convertis.
-- Sans cette garde, la commune sortait sous l'adresse `ville_0` et l'URL du
-- legacy (`/elections/resultats/dordogne-24/ville_faux`) rendait 404.
-- `cities.nom_standard` est sain pour ces deux lignes, et
-- LOWER(nom_sans_accent) redonne exactement le slug que la production sert.
--
-- Le CASE sur Étoile (L') et Nonières (Les) compense un autre décalage : la
-- production a nettoyé ces deux slugs (datan.fr sert `ville_etoile` et
-- `ville_nonieres`) quand une copie gardait `etoile-(l)` et `nonieres-(les)`.
-- L'adresse du site est le contrat. Ne pas généraliser aux 22 autres slugs
-- parenthésés : Assions (Les), Ollières-sur-Eyrieux (Les) et Bonvillers (Mont)
-- sont liées AVEC leurs parenthèses par datan.fr, les autres nulle part.
SELECT c.insee, c.dpt,
       COALESCE(NULLIF(c.commune_nom, '0'), ci.nom_standard) AS commune_nom,
       CASE c.insee
         WHEN '39217' THEN 'etoile'
         WHEN '07165' THEN 'nonieres'
         ELSE COALESCE(NULLIF(c.commune_slug, '0'), LOWER(ci.nom_sans_accent))
       END AS commune_slug,
       MAX(ci.population) AS population,
       GROUP_CONCAT(DISTINCT c.circo ORDER BY CAST(c.circo AS UNSIGNED)) AS circos,
       MAX(i.postal) AS code_postal,
       MAX(cin.pop2012) AS population_2012
FROM circos c
LEFT JOIN cities ci ON ci.code_insee = c.insee
LEFT JOIN insee i ON i.insee = c.insee
LEFT JOIN cities_infos cin ON cin.insee = c.insee
GROUP BY c.insee, c.dpt, commune_nom, commune_slug
ORDER BY c.dpt, commune_nom;
