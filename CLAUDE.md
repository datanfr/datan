# Datan

Site de décryptage des votes de l'Assemblée nationale française. Ce dépôt **est**
Datan : ce n'est pas une migration en cours vers autre chose. `../datan`
(CodeIgniter 3) est l'application actuellement en production et sert de
**référence fonctionnelle** — quand une question se pose sur un comportement, la
réponse est dans son code, pas dans une intuition.

Le site public de référence est <https://datan.fr/>. Toute page portée doit lui
être **visuellement identique** et **aussi rapide**. Ce sont les deux exigences
non négociables.

Identique ne veut pas dire miroir : **parité sur ce qui est un choix, correction
sur ce qui est un défaut.** Un tri, une formulation, un seuil se reproduisent tels
quels, même s'ils surprennent. Une coquille, un accord manqué, un lien vide, une
ancre sans son décalage se corrigent — et le commentaire dit pourquoi, sinon le
prochain lecteur croira à une erreur de portage.

`TODO.md` tient l'état du reste à faire, défauts ouverts compris.

## Ce qui fait la valeur du site

Le **décryptage des votes** : une couche éditoriale écrite par la rédaction
par-dessus les scrutins bruts (entités `Decryptage`, `Categorie`, `Lecture`).
Elle n'est pas dérivée de l'open data et ne peut pas être régénérée — les
décryptages déjà produits sont à préserver comme des données de production.

## Conventions

- **Français partout** : noms de commandes, de méthodes privées, de variables
  locales, commentaires, messages console. Les noms d'entités et de colonnes
  suivent le vocabulaire de l'Assemblée (`scrutin`, `organe`, `mandat`).
- **Pas d'ORM sur les pages de lecture.** Les contrôleurs interrogent DBAL et
  rendent des tableaux associatifs. Aux volumes en jeu (1,2 M de votes),
  hydrater des entités coûte plusieurs ordres de grandeur. L'ORM sert au
  schéma et aux écritures unitaires.
- **Cache HTTP sur toute page publique** : `setPublic()` +
  `setSharedMaxAge()`. Le reverse proxy intégré est branché dans
  `public/index.php` hors debug. Une page en cache doit sortir en ~20 ms.
  La durée est d'un jour (`RechauffeurCache::DUREE_PAGES`) : la fraîcheur
  vient des **purges**, pas de l'expiration — après le sync quotidien, et après
  toute écriture d'un contenu publié (décryptage, explication, article), qui
  appelle `RechauffeurCache::apresPublication()`. Une nouvelle écriture
  éditoriale visible sur le site doit l'appeler aussi, sinon elle n'apparaît
  que le lendemain. `app:cache:rechauffer` remet ensuite en cache les ~2 500
  pages les plus lues, communes exclues.
- **Commentaires** : expliquer *pourquoi*, pas *quoi*. Les pièges métier de ce
  domaine sont nombreux et non devinables — ce sont eux qu'il faut consigner.
- **Une entité `#[ApiResource]` s'expose en écriture par défaut.** Sans
  `operations:`, API Platform ajoute POST, PATCH et DELETE, et n'exige aucune
  authentification. Notre API est publique **en lecture seule** : toute nouvelle
  entité exposée déclare `operations: [new Get(), new GetCollection()]`. Les
  écritures passent par l'espace de rédaction, le tableau de bord des députés ou
  les commandes d'import — jamais par `/api`.

## Routage et adresses

- **Les URL du legacy sont un contrat.** Elles sont indexées depuis des années ;
  Datan vit de son référencement. Une adresse ne change pas — et si notre donnée
  produit une autre forme, on **redirige en 301** vers celle du site plutôt que
  de rendre un 404 (`DepartementController::versLAdresseCanonique()`).
- **Rien ne garantit l'ordre entre deux contrôleurs.** Les routes viennent
  d'attributs, chargées fichier par fichier : une route large en attrape une
  précise déclarée ailleurs. Deux parades selon le cas — une contrainte
  d'exclusion (`DepartementController::SLUG` écarte `legislature-` et
  `inactifs`), ou `priority:` quand l'adresse précise appartient à un autre
  contrôleur (`/statistiques/aide` face à `/statistiques/{page}`, qui accepte
  `[a-z\-]+` et l'avalerait). Le legacy règle les mêmes collisions par l'ordre
  de `routes.php` : sa lecture dit lesquelles existent.
- **Un sitemap n'annonce que ce qui répond 200.** Promettre des 404 coûte plus
  que de se taire. Chaque liste de `SitemapController` reprend donc le critère
  de sélection de la page correspondante — et se vérifie en interrogeant
  réellement chaque adresse, pas en relisant la requête.
- **Deux jeux de slugs de département coexistent.** `depute.dpt_slug` est
  fabriqué (nom + code) ; `departement.slug` vient de la table tenue à la main du
  legacy, et c'est lui l'adresse de datan.fr — `francais-de-letranger`, pas
  `francais-etablis-hors-de-france-099`. Cinq diffèrent. Bâtir un lien depuis
  `dpt_slug` sans vérifier expose à servir une adresse que la route refuse.

## Imports

- **Toujours `APP_ENV=prod`.** En dev, le logger Doctrine conserve chaque
  requête en mémoire et fait échouer tout import volumineux. La variable
  d'environnement seule ne suffit pas pour un serveur de test : il faut un
  `.env.local` temporaire, **à supprimer ensuite**.
- **Upsert par lots** (`INSERT … ON DUPLICATE KEY UPDATE`), maps de
  correspondance via `fetchAllKeyValue`, lecture en flux.
- **Un upsert ne doit jamais écraser avec du vide.** L'open data publie
  irrégulièrement : une colonne absente d'une moisson ne veut pas dire que
  l'information est fausse. Utiliser `$misAJourSiRenseigne` d'
  `ImportTricoteusesCommand`, qui traduit en `COALESCE(VALUES(c), c)`.
  Exception : ce qu'on sait faux se purge **avant** d'apparier, sans quoi la
  garde protège l'erreur pour toujours.
- **Ne jamais lancer `cache:clear` pendant une synchronisation.** Vider
  `var/cache/prod` sous une commande en cours la fait échouer sans message
  exploitable — code 1 muet, ou `Failed opening required …Service.php`. Le
  symptôme ne ressemble en rien à sa cause.
- **Lier un `false` PHP, c'est lier une chaîne vide.** Le pilote mysqli ne
  connaît pas le booléen : MariaDB refuse alors la valeur dans une colonne
  entière (« Incorrect integer value: '' »). Passer des `0`/`1`, et garder le
  `null` quand l'absence de valeur a un sens — un candidat dont on ignore
  encore s'il est élu n'est pas un candidat battu.
- **Compter les lignes écartées, et dire pourquoi.** Un import qui lit trois
  millions de lignes et en écrit moins doit rendre l'écart à l'unité : sans ce
  bilan, une règle fausse ressemble en tout point à une source incomplète.

## Source des données : les Tricoteuses

Les Tricoteuses n'ont pas d'API HTTP : elles publient des **dépôts Git de
fichiers JSON** remoissonnés chaque nuit depuis l'open data de l'Assemblée,
sous <https://git.tricoteuses.fr/assemblee> — **et non plus `git.en-root.org`**,
abandonné à l'été 2026 sans avertissement dans les dépôts de données : il
répond toujours, figé au 22 juillet, et un sync qui le lit réussit chaque soir
sans rien apporter (`Catalogue`). Git est l'interface : il donne
le transfert incrémental et la liste exacte des fichiers modifiés.

```
php bin/console app:sync:quotidien          # moisson + imports (~50 s à vide, 9 étapes)
php bin/console app:sync:quotidien --tout   # reconstruction complète
php bin/console app:tricoteuses:sync --liste
```

Dépôts déclarés dans `App\Tricoteuses\Catalogue`, clonés dans
`var/tricoteuses/`. Le moissonneur y dépose un fichier `.datan-modifies` que
les imports consomment pour ne traiter que le delta. Planification Windows :
`bin\planifier-sync.ps1`.

**Tout ne vient pas des Tricoteuses.** Restent à la charge de Datan : le lien
scrutin → dossier et scrutin → amendement (l'Assemblée ne le publie que depuis
2026, et sur 75 % des scrutins seulement — le legacy le scrape sur
`assemblee-nationale.fr/dyn/`), le détourage des photos, les parrainages et les
réseaux sociaux des députés.

## Seconde source : la base de production

Ce que l'open data ne publie pas vit dans la base de l'application d'origine,
qui n'est pas joignable depuis ici — le port 3307 sert la base de
développement. On passe donc par des exports TSV, et **chaque commande porte
dans son docblock la requête `docker exec` qui régénère les siens** : sans
cela, l'import n'est plus rejouable dès que le fichier est perdu. Socle commun :
`ImportLegacyCommand` (lecture en flux, upsert par lots, `COALESCE`).

```
php bin/console app:import:communes             # départements, communes, voisinages
php bin/console app:import:elections            # catalogue des scrutins, candidatures
php bin/console app:import:resultats-electoraux # 3,1 M de résultats par commune (105 s)
php bin/console app:import:utilisateurs         # comptes de connexion (mot de passe repris)
php bin/console app:import:explications         # explications de vote des députés
php bin/console app:import:articles             # blog (rubriques + articles)
```

Aucune n'est dans `app:sync:quotidien` et aucune n'a à y entrer : un scrutin
passé ne change plus, une commune non plus. On les lance sciemment.

- **Le contenu éditorial de la production se préserve, il ne se régénère pas.**
  Comptes, explications de vote, blog, FAQ, quiz, décryptages : rien de tout
  cela n'est dans l'open data. Ce sont des données de production, à récupérer
  telles quelles. **Le mot de passe d'un compte se reprend sans le réinitialiser**
  — le legacy hache en `password_hash(PASSWORD_DEFAULT)`, du bcrypt que le
  vérifieur `auto` de Symfony relit et ré-encode tout seul.
- **Un import de récupération réaligne sur la production.** Il est fait pour le
  chargement initial ; une fois l'application devenue la source (un rédacteur qui
  publie, un député qui écrit), le rejouer écraserait les écritures locales. À ne
  pas glisser dans une tâche planifiée.
- **Le backup public `datan.fr/assets/dataset_backup` est réduit et anonymisé** :
  un seul compte, `users_mp` vide, `decryptage.created_by` non résolvable. Les
  commandes de récupération s'y vérifient mais se rejouent contre la vraie base
  des comptes au déploiement.
- **Résoudre le scrutin d'une explication par le décryptage, pas par le numéro.**
  Un vote du Congrès porte le numéro sentinelle `-1` dans `explications_mp` ;
  chercher `scrutin.numero = -1` ne trouve rien et perd les explications (les 5
  de l'IVG en L16). La table `decryptage` porte le même `-1` et le bon
  `scrutin_id` : passer par elle.

Ce qui est **délibérément laissé de côté** : les tables `elect_bv_*` (grain
bureau de vote, créées en mars 2026 et jamais écrites — la page ville du legacy
y lit du vide) et les municipales 2026, que le legacy traite sous une élection
`id = 7` absente de son propre catalogue. C'est son chantier en cours, pas une
donnée à porter.

## Pièges métier à ne pas réinventer

- **`depute.groupe_id` et `depute.parti_id` ne portent que l'appartenance
  courante** — un rattachement encore ouvert, jamais le dernier connu. Un
  ancien député n'a donc ni groupe ni parti. Pour un groupe à une législature
  passée, ou pour la composition au moment d'un scrutin, passer par
  `fonction_groupe`, `mandat` ou `vote_groupe`. Recalculer une ventilation
  depuis `vote` + `groupe_id` donne des totaux faux.
- **Rattachement d'un député à un groupe** : le plus récent — encore ouvert
  d'abord, puis date de fin la plus tardive (`daily.php:914`). Pas le plus long.
- **`fonction_groupe.nomin_principale` n'est pas décoratif.** Onze députés de la
  17e législature portent deux rattachements ouverts ; sans ce filtre ils
  comptent dans deux groupes. Les mandats de groupe ouverts sont 588, dont 577
  principaux — le nombre exact de sièges.
- **Composition d'un groupe dissous** : celle du jour de sa disparition
  (`date_fin` du groupe, `BETWEEN` sur les bornes du rattachement). Les
  apparentés (`code_qualite = 'Membre apparenté'`) comptent dans l'effectif
  mais s'affichent à part.
- **Proximité entre deux groupes** : part des scrutins où **tous deux ont voté
  pour, ou tous deux contre** (`daily.php:2176`). Deux abstentions ne valent pas
  accord, et il n'y a pas d'accord partiel — c'est 0 ou 1. Contre-intuitif,
  mais c'est le chiffre affiché depuis toujours.
- **Une coalition** est l'ensemble des groupes ayant pris la même position
  majoritaire sur un scrutin, « pour » ou « contre » — l'abstention n'en forme
  pas une, et les non-inscrits en sont exclus : ils ne constituent pas un
  groupe. Le partage en blocs politiques qui commente ces coalitions
  (`App\BlocPolitique`) est une lecture éditoriale, valable pour la seule 17e
  législature, et LIOT n'y appartient à aucun bloc.
- **La 17e législature n'a aucun groupe « Majoritaire ».** Depuis la dissolution
  de 2024, l'Assemblée ne déclare plus de `positionPolitique` sur ses groupes :
  tout bloc qui compare à la majorité présidentielle doit disparaître au lieu de
  se rabattre sur un groupe choisi au jugé.
- **Commission d'un député** : celle où il a cumulé le plus de jours sur sa
  dernière législature, et affichée par le `libelleAbrege` de l'organe
  (« Lois »). Prendre le mandat COMPER ouvert donne les remplacements de
  quelques jours.
- **Participation d'un député** : votes exprimés sur les scrutins solennels
  (`code_type_vote = 'SPS'`) rapportés à *tous* les solennels tenus pendant sa
  période d'activité. Ne compter que les scrutins où il a une ligne `vote`
  donne faussement 100 %.
- **Un non-votant n'est pas un votant qui s'abstient.** Dans la ventilation d'un
  scrutin, `nonVotants` — présidence de séance, membres du Gouvernement, qui
  n'ont pas le *droit* de voter — désigne un ensemble **disjoint** des pours,
  contres et abstentions : le `decompteNominatif` les liste à part. Les
  `nonVotantsVolontaires`, eux, sont bien *inclus* dans les abstentions. D'où la
  participation d'un groupe : `(pours + contres + abstentions) / (membres −
  nonVotants)` — on les retire du **dénominateur**, faute d'avoir pu voter, et
  jamais du numérateur, où ils n'ont jamais été comptés. Le legacy se trompe de
  côté sur sa seule page de vote et affiche des participations trop basses,
  voire nulles ; cf. `App\Groupe\ParticipationGroupe`.
- **`date_fin` d'un député n'est pas fiable** pour savoir s'il siège : se fonder
  sur l'existence d'un mandat sans date de fin.
- **Les votes nominatifs couvrent les législatures 14 à 17** (2,46 M lignes)
  depuis l'import des dépôts `Scrutins_XIV/XV/XVI_nettoye` — clos, déclarés
  `quotidien: false` dans le `Catalogue`, importés une fois pour toutes par
  `app:import:scrutins --depot=scrutins-xiv --tout` et jamais par le sync.
  Toute statistique qui compte des votes DOIT filtrer la législature : un
  réélu porte les lignes de quatre législatures. Et après un tel import, les
  calculs se rejouent **par législature** :
  `app:calcul:statistiques-deputes --legislature=N`, qui ne réécrit que la
  sienne — les lignes des législatures closes survivent au recalcul quotidien
  de la courante.
- **`dec` est un mot réservé MariaDB** : ne pas s'en servir comme alias.
- **Un vote du Congrès porte le même numéro qu'un scrutin de l'Assemblée.** Le
  scrutin n° 1 de la 16e législature existe deux fois ; seul le préfixe d'uid
  (`VTANR` / `VTCGR`) les distingue. Le Congrès vit sous `vote_c<n>`, et
  `decryptage.vote_numero` le note **négatif**. D'où la fonction Twig
  `lien_vote(legislature, numero)` : appeler `path('vote_individual')` sur un
  numéro venant de la base lève une 500 dès qu'il est négatif.
- **L'article d'un département est une donnée, pas une règle.** `departement.libelle_de`
  vaut « du », « de la », « des », « de l' » — et **porte son espace final**
  quand il en faut un, le libellé se concaténant directement au nom. 86 des
  107 en ont un : les rogner casse la moitié des titres. Ni cet article ni la
  liste des communes ne sont dans l'open data ; ils viennent de la base de
  production (`app:import:communes`).
- **La Corse n'a pas la même casse d'une colonne à l'autre.** `commune.code_insee`
  l'écrit en minuscules — `2a004` pour Ajaccio — quand `departement.code` écrit
  `2A`. Or **les collations de MariaDB ignorent la casse** : une jointure, un
  `WHERE`, un `LIKE` retrouvent toujours leur ligne, et rien ne se voit. Ce qui
  s'y casse les dents est en dehors de la base — un `isset()` en PHP, une route
  qui exige `[a-z0-9\-]+`. Comparer à l'identique fait **disparaître les 360
  communes corses en silence**, et la perte ne se constate qu'en comptant.
  Indexer les référentiels en minuscules et rendre l'orthographe de la base ;
  pour seulement voir le problème depuis SQL, il faut un `REGEXP BINARY`.
- **Le « département » 099 n'est pas de l'outre-mer** : ce sont les Français
  établis hors de France, et leur code fait six caractères — `099069` désigne
  Dubaï. Les règles taillées pour l'outre-mer, appliquées à lui, font atterrir
  des pays entiers sur des communes de l'Ariège (`099` + `233` → `09233`).
- **Le code INSEE d'une commune d'outre-mer ne s'écrit pas partout pareil.** Les
  fichiers du ministère répètent le troisième chiffre du département dans le
  premier du code de commune, et les deux tables ne renseignent pas le même :
  la présidentielle écrit Mayotte `976` + `503`, les européennes `976` + `603`,
  pour la même commune `97603`. Ne jamais recomposer un code par une règle de
  découpage seule : essayer les écritures possibles et **laisser le référentiel
  arbitrer**, sans jamais inventer un code. Reste un cas insoluble à la source,
  Saint-Barthélemy et Saint-Martin, qu'elle range indifféremment sous 977 et 978.
- **Un slug dérivé d'un libellé n'est pas unique.** Deux organes peuvent porter
  le même nom : « Affaires économiques » existe en `PO277060` (clos en 2009) et
  `PO419610` (en activité), idem pour les affaires culturelles. Avec un index
  unique sur le slug, l'`ON DUPLICATE KEY UPDATE` d'un import **écrase
  silencieusement la ligne homonyme** — l'organe perdu disparaît de la table de
  correspondance, et tous ses mandats sont rejetés sans la moindre erreur
  (3 362 sur 25 118 la première fois). Traiter les organes en activité d'abord,
  pour qu'ils gardent l'adresse simple, et suffixer le doublon.

## Environnement

Base **MariaDB 11.7.2** sur `127.0.0.1:3307` (`datan` / `datan` /
`datan_symfony`). Le `serverVersion` du DSN doit dire `11.7.2-MariaDB`, sinon
Doctrine génère des diffs de schéma fantômes. La base de l'application en
production vit dans le conteneur Docker `datan-db` :
`docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan` (forcer
`-h`, et utiliser le client `mariadb`).

**Vérifier `.env.local` avant de conclure quoi que ce soit d'un 404.** Ce fichier
va et vient ; quand il pose `APP_ENV=prod`, une route neuve répond 404 tant que
`var/cache/prod` n'a pas été reconstruit, et le conteneur compilé fait foi contre
le code. Le signe distinctif est la page d'erreur : celle de production (« Oops!
An Error Occurred ») et non celle de débogage. Remède :
`rm -rf var/cache/prod && php bin/console cache:warmup`. Le fichier appartient à
l'utilisateur — ne pas le supprimer sans le lui dire ; en revanche, un `.env.local`
créé pour un essai se supprime toujours.

**Changer la signature d'un constructeur périme le conteneur prod en silence.**
Le conteneur compilé injecte le nombre d'arguments qu'il connaissait à la
compilation : un service ajouté à un contrôleur fait tomber toutes ses pages en
500 (`ArgumentCountError`) tant que `var/cache/prod` n'est pas reconstruit — et
le symptôme ressemble à un défaut du code fraîchement écrit, pas à un cache.
Avec plusieurs agents dans le même arbre, celui qui modifie un constructeur
réchauffe le cache lui-même, sitôt le changement posé (jamais pendant un import
en cours, cf. plus haut).

**`APP_ENV=dev php -S …` ne suffit pas à sortir de prod.** Le runtime Symfony
lit `$_SERVER['APP_ENV']`, et le `variables_order=GPCS` du php.ini local n'y
verse pas l'environnement : le préfixe est ignoré sans un mot et `.env.local`
gagne. Le signe : l'en-tête `Age:` (HttpCache) au lieu de `X-Debug-Token`.
Remède : `php -d variables_order=EGPCS -S …`.

**Un banc d'essai à plusieurs `php -S` fabrique de faux 500.** Ces serveurs sont
mono-processus : pour interroger beaucoup d'adresses, on en lance plusieurs, et
ils partagent alors le même magasin `var/cache/prod/http_cache`, dont les verrous
lâchent sous concurrence. Les anomalies sortent alors par tranches contiguës —
signature qu'un vrai défaut n'a pas. **Toujours rejouer les adresses signalées une
à une sur un seul serveur avant de conclure** : 62 sur 26 875 ont ainsi été
disculpées d'un coup.
