-- app:import:communes — maires (« Le maire d'Ajaccio est … » des fiches de ville).
SELECT insee, nameFirst, nameLast, gender
FROM cities_mayors
ORDER BY insee;
