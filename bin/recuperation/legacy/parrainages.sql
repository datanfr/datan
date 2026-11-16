-- app:import:parrainages — parrainages de la présidentielle 2022, en entier :
-- le bloc des candidats à plus de 500 signatures se calcule sur tous les élus.
SELECT id, civ, nameLast, nameFirst, mandat, circo, dpt, candidat,
       datePublication, year, mpId, dateMaj
FROM parrainages
ORDER BY id;
