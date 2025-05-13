# Migration DB

## Amendement
Amendement : 
vérifier les enregistrements orphelins : 
```SELECT a.*
FROM amendements a
LEFT JOIN dossiers d ON a.dossier = d.dossierId
WHERE d.dossierId IS NULL;
```
Supprimer les lignes ou ajouter un dossier valide

puis
```
ALTER TABLE `amendements` ADD FOREIGN KEY (`dossier`) REFERENCES `dossiers`(`dossierId`) ON DELETE RESTRICT ON UPDATE RESTRICT;
```

Amendement auteur :
Il faudrait rajouter dans deputes_all le mpID PO855052 car on ne peut créer de clé orpheline
Si ce n'est pas un depute, peut-être qu'on peut rajouter une colonne "type" ? Ca permettrait d'ajouter d'autres acteurs
Ou alors créer une table "acteur" avec type et tous les deputes et autres acteurs ensemble
```
ALTER TABLE `amendements_auteurs` ADD FOREIGN KEY (`acteurRef`) REFERENCES `deputes_all`(`mpId`) ON DELETE RESTRICT ON UPDATE RESTRICT;
```

## Géographie
Tables de géographie :
Refactorisation
En gros on clean en supprimant ces tables :
`circos`
`cities_adjacentes`
`cities_infos`
`cities_mayors`
`departement`
`insee`
`regions`

Et on les refait comme ça :

```
CREATE TABLE regions (
  region_id INT PRIMARY KEY,           -- identifiant de la région
  name VARCHAR(45) NOT NULL,            -- nom de la région (ancien libelle)
  cheflieu VARCHAR(8),                  -- chef-lieu de la région
  tncc VARCHAR(4),
  ncc VARCHAR(26),
  nccner VARCHAR(45)
);
CREATE TABLE departments (
  department_id INT PRIMARY KEY,       -- identifiant interne
  region_id INT,                       -- référence vers la région
  code VARCHAR(15),                    -- code départemental
  name VARCHAR(255),                   -- nom du département
  slug VARCHAR(255),                   -- slug pour l’URL
  nom_soundex VARCHAR(20),             -- nom soundex
  additional_label1 VARCHAR(255),      -- libellé additionnel (ex : libelle_1)
  additional_label2 VARCHAR(255),      -- libellé additionnel (ex : libelle_2)
  FOREIGN KEY (region_id) REFERENCES regions(region_id)
);
CREATE TABLE communes (
  insee VARCHAR(15) PRIMARY KEY,   -- code INSEE, identifiant unique
  department_id INT,               -- référence vers le département
  name VARCHAR(45) NOT NULL,       -- nom de la commune (ex : commune_nom)
  slug VARCHAR(100),               -- slug de la commune
  postal VARCHAR(30),              -- code postal
  FOREIGN KEY (department_id) REFERENCES departments(department_id)
);
CREATE TABLE electoral_districts (
  electoral_district_id INT AUTO_INCREMENT PRIMARY KEY,
  circo VARCHAR(21),      -- identifiant de la circonscription
  canton VARCHAR(11),     -- code du canton
  canton_name VARCHAR(38) -- nom du canton
);

### table d'association
CREATE TABLE commune_electoral_districts (
  insee VARCHAR(15),
  electoral_district_id INT,
  PRIMARY KEY (insee, electoral_district_id),
  FOREIGN KEY (insee) REFERENCES communes(insee),
  FOREIGN KEY (electoral_district_id) REFERENCES electoral_districts(electoral_district_id)
);

CREATE TABLE populations (
  insee VARCHAR(15),
  year INT,
  population INT,
  PRIMARY KEY (insee, year),
  FOREIGN KEY (insee) REFERENCES communes(insee)
);

CREATE TABLE mayors (
  mayor_id INT AUTO_INCREMENT PRIMARY KEY,
  insee VARCHAR(15),
  last_name TEXT,           -- nom de famille
  first_name TEXT,          -- prénom
  gender VARCHAR(2),
  birth_date DATE,
  profession SMALLINT,
  profession_label TEXT,
  update_date DATE,         -- date de mise à jour (dateMaj)
  FOREIGN KEY (insee) REFERENCES communes(insee)
);

CREATE TABLE adjacent_communes (
  insee VARCHAR(15),
  adjacent_insee VARCHAR(15),
  PRIMARY KEY (insee, adjacent_insee),
  FOREIGN KEY (insee) REFERENCES communes(insee),
  FOREIGN KEY (adjacent_insee) REFERENCES communes(insee)
);
```
Côté code, il va falloir beaucoup de refactoring
Dans toutes les autres tables, il faudra rajouter les foreign keys, renommer des colonnes... etc...
TODO : Identifier les tables qui font appel à des tables de géographie

## Supprimables
Class_group
A voir pour la supprimer si le calcul peut se faire en procédure stocker ou dans le code,
comparer les performances

class_groups_month
rajouter ID et foreign key organeRef vers parti
Au daily ne pas la supprimer mais mettre à jour 

Même chose pour tous les _class

## Tables députés
Voir pour les regrouper

## clés étrangères

## Votes
 ALTER TABLE `votes_info` ADD PRIMARY KEY(`voteId`)
 ALTER TABLE `votes_info` ADD FOREIGN KEY (`legislature`) REFERENCES `legislatures`(`legislatureId`)
 ALTER TABLE `votes_info` ADD FOREIGN KEY (`mpId`) REFERENCES `deputes_all`(`mpId`)