# Datan
Datan is a website anlaysing the voting behaviour of French MPs.
Datan est un site internet analysant les votes des députés français (Assemblée nationale).

# Discord
Si vous voulez contribuer ou que vous avez besoin d'aide, n'hésitez pas à rejoindre. https://discord.gg/79E6SB7W

***
# Installation Docker
Suivez les étapes suivantes pour installer Datan avec Docker.

## 1. Installez docker et docker-compose
https://docs.docker.com/compose/install/

## 2. Créer .env
Copiez .env.dist en .env

```
cp .env.dist .env
```

### Créer une URL personnalisée (optionnel)
Modifiez BASE_URL par "dev-datan.fr"
Ajoutez la ligne suivante dans votre fichier /etc/hosts
```
#datan
127.0.0.1 dev-datan.fr
```
Cela vous permettra d'accéder au site en tapant dev-datan.fr au lieu de localhost

## 3. Copie du fichier de configuration
Copiez le fichier custom_config.php.dist en custom_config.php

```
cp application/config/custom_config.php.dist application/config/custom_config.php
```

## 4. Photos des députés
Le projet doit télécharger plusieurs fois toutes les photos des députés (environ 4.000 photos). Pour alléger l'installation, il est possible de lancer le projet sans l'installation des photos. Pour cela :

- Ne rien changer au fichier daily.php.
- Vérifiez dans `application/config/custom_config.php` que `$config['mp_photos']` est bien à `FALSE`.

Si vous souhaitez télécharger les photos :

- Décommentez la ligne `$this->mp_photos = TRUE;` dans le fichier `scripts/daily.php`
- Dans le fichier `application/config/custom_config.php`, mettez `$config['mp_photos'] = TRUE`.

## 5. Builder le projet

```
docker-compose build
```

En fonction de la version de docker : 

```
docker compose build
```

## 6. Lancer le projet
```
docker-compose up
```
Pour le premier lancement, il faut attendre quelques minutes que la base de données se charge complètement avant de lancer la commande suivante.

En fonction de la version de docker : 

```
docker compose up
```

## 7. Mettre à jour la base de données
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

***

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


# Accéder au dashboard
Pour accéder au dashboard, vous devez créer un utilisateur.
application/controllers/Users.php, commentez la ligne 78 :
```php
// redirect(); // The register system is for now opened only for MPs.
```
Une fois inscrit (vous aurez peut-être une erreur Mailjet mais c'est pas grave), votre utilisateur sera dans la base de données.
Il suffira alors de vous connecter à phpMyAdmin et modifier la colonne type pour votre utilisateur en "admin".
Déconnectez-vous et connectez-vous à nouveau et vous aurez accès à /admin


# Test de branche
```php compare_branch.php fichier_url branche_a_tester```
exemple en étant sur votre branche à tester: 
```npm run docker-test -- master```
exemple en étant sur votre branche à tester: 
```npm run docker-test -- mon_nom_de_branche```