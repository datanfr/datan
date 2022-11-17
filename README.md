# Datan
Datan is a website anlaysing the voting behaviour of French MPs.
Datan est un site internet analysant les votes des députés français (Assemblée nationale).

# Installation Docker
Suivez les étapes suivantes pour installer Datan avec Docker.

## 1. Installez docker et docker-compose
https://docs.docker.com/compose/install/

## 2. Créez .env
Copiez .env.dist en .env

### (facultatif) a. URL personnalisée
Modifiez BASE_URL par "dev-datan.fr"
Ajoutez la ligne suivante dans votre fichier /etc/hosts
```
#datan
127.0.0.1 dev-datan.fr
```
Ca vous permettra d'accéder au site en tapant dev-datan.fr au lieu de localhost


## 3. Builder le projet
```
docker-compose build
```

## 4. Lancer le projet
```
docker-compose up
```
Pour le premier lancement, il faut attendre quelques minutes que la base de données se charge complètement avant de lancer la commande suivante.

## 5. Mettre à jour la base de données
Commandes à faire la première fois et à chaque fois que vous voulez rafraichir les données
```
npm run docker-download
npm run docker-daily
```

## Aide Docker
Arrêtez les instances avec Ctrl+C
Pour fermer les instances ```docker-compose down```
Reinstaller et retélécharger la base de données avec ```docker-compose build```

## Assets
Générez les assets avec ```npm run docker-grunt```
Ou en continue avec ```npm run docker-grunt-watch # (ou npm run dgw)```

## PHPMyAdmin
Vous pouvez y accéder ici : http://localhost:8080/ ou http://dev-datan.fr:8080/

# Installation from scratch
Suivez les étapes suivantes pour installer Datan en local.


## 1. Assets
* Installez les dépendances en lancant les commandes suivantes :

```
npm install --dev  
composer install  
```
* Pour **Windows**, il faut également installer Ruby et lancer la commande suivante. Si problème de permission, supprimer le dossier *.sass-cache*.

```
gem install sass
```
* Lancer **Grunt** pour compiler les fichiers css et js avec la commande suivante.

```
grunt  
```
* NB : Quand vous travaillez sur le fichier *main.scss* ou le fichier *main.js*, lancez la commande suivante pour compiler automatiquement les fichiers css et js.

```
grunt watch
```

## 2. Base de données
* Importer le fichier SQL principal. Le fichier contient la structure de la base de données, ainsi que les données essentielles. Importer le fichier de backup le plus récent se trouvant dans le dossier suivant : *https://datan.fr/assets/dataset_backup/general/*
* Plus d'infos sur la base de donnée se trouve dans le dossier suivant : https://github.com/datanfr/datan/tree/master/scripts/update_dataset/infos 

## 3. Variables environnement
* Dupliquer *.htaccess.dist* et renomer le nouveau fichier en *.htaccess*  
* Remplir les variables *SetEnv* (exemple : DATABASE_USERNAME, DATABASE_PASSWORD, etc). Les variables concernant les API suivantes ne sont pas nécessaires pour que le site Datan fonctionne en local (NOBG, MAILJET, MJML).

## 4. Remplir la base de données
* Lancer les scripts suivants via la ligne de commande :

```
php /scripts/download.php  
php /scripts/daily.php  
php /scripts/daily.php 15  
php /scripts/daily.php 14  
```
## 5. Problème supplémentaire avec Windows
* Si problème pour le lancement de la newsletter: https://stackoverflow.com/questions/21114371/php-curl-error-code-60  
