-- app:import:resultats-electoraux — présidentielle (second tour), par commune.
SELECT election, dpt, commune, candidate, voix, share, votants, abs_pct
FROM elect_pres_2
ORDER BY dpt, commune;
