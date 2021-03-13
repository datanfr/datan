# datan
 Datan is a website anlaysing the voting behaviour of French MPs.

# Installation
Copy .htaccess.dist to .htaccess
Fill in the SetEnv variables (replace username_example and password_example)

## Base de données
Importer la structure de la base
Puis les données de ces tables :
-departement.sql
-circos.sql
-cities_infos.sql
-regions_old_new.sql
-insee.sql
-categories.sql
-fields.sql
-elect_2019_europe_listes.sql
-elect_2019_europe_clean.sql
-elect_2019_europe.sql
-elect_2017_pres_2.sql

Puis lancer ces scripts depuis /scripts/admin.php

# Assets
Installez les dépendances
npm install --dev
Lancez grunt pour compiler les fichiers scss et js
grunt
Qaund vous travaillez sur main.scss ou main.js lancez grunt watch pour que ça recompile à chaque changement
grunt watch 
## Windows
Il faut au préalable installer Ruby et possiblement lancer "gem install sass"
Si problème de permissions supprimer le dossier .sass-cache