# datan
 Datan is a website anlaysing the voting behaviour of French MPs.

# Installation
Copy .htaccess.dist to .htaccess  
Fill in the SetEnv variables (replace username_example and password_example)  

## Base de données
Importer la structure et les données essentielles de la base de données.  
Le fichier SQL est le suivant : *scripts/update_dataset/datan_database_[date].sql*

Puis lancer ces scripts :  
php /scripts/daily.php  
php /scripts/daily.php 14  

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
