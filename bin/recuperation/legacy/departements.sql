-- app:import:communes — départements. `libelle_1`/`libelle_2` portent leur
-- espace final quand il en faut un (« du », « de la ») : ne pas rogner.
SELECT departement_code, departement_nom, slug, libelle_1, libelle_2, region
FROM departement
ORDER BY departement_code;
