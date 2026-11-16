-- app:import:elections — candidatures des députés, relevées à la main et
-- modifiables par le député depuis son tableau de bord.
--
-- Pas d'ORDER BY, et c'est voulu : l'ordre physique de elect_deputes_candidats
-- (l'ordre de saisie) fait l'ordre d'affichage des députés candidats au legacy,
-- que le port garde dans `candidature.ordre_saisie`. Un tri ici le détruirait.
SELECT mpId, election, candidature, district, position, nuance,
       visible, secondRound, elected, link, source
FROM elect_deputes_candidats;
