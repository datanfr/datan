# TODO — ce qu'il reste à faire

Seulement l'ouvert. Ce qui est fait vit dans l'historique git ; les écarts
voulus avec datan.fr, à ne pas « corriger », sont dans `DIFFERENCES.md`.

Rappel de méthode : datan.fr répond 429 en rafale — une URL à la fois, 2 s
d'écart — et garde ses fiches en cache jusqu'à trois jours : un écart de
compteur peut n'être qu'un écart de fraîcheur. La règle reste celle de
`CLAUDE.md` : parité sur ce qui est un choix, correction sur ce qui est un
défaut, et la réponse est dans le code du legacy.

---

## 1. Mise en production des corrections du 2026-11-16

Sur le serveur, dans `~/symfony.datan.fr/current`, `php` = `~/symfony.datan.fr/bin/php`,
toujours `APP_ENV=prod`, **jamais pendant une synchronisation** (cron à 6 h,
6 h 20 une fois l'étape 2 faite). Les étapes 3 à 11 écrivent dans la base.

- [ ] **0. Avant** : IP du poste dans la liste blanche SSH d'o2switch (elle en
  sort régulièrement) ; sauvegarde du jour vérifiée (`shared/sauvegardes`).
- [ ] **1. Déployer** (push sur `symfony`). Quatre migrations partent avec le
  code, et doivent partir avec lui : `…100000` (comptes, journal de
  récupération), `…110000` (mots-clés, lots IA), `…120000` (municipales),
  `…140000` (clé des mandats — l'ancien import, sans elle, insérerait des
  doublons). *Contrôle* : `doctrine:migrations:status` à jour, accueil en 200.
- [ ] **2. Cron** : `bash bin/cron.sh afficher`, puis `installer`.
  *Contrôle* : `crontab -l | grep "20 6"`.
- [ ] **3. Synchronisation complète** : `php bin/console app:sync:quotidien --tout --no-ansi`
  (12 à 25 min). Ramène les scrutins 9156-9200, les 223 amendements manquants
  et les auteurs ; rattache les 3 839 mandats à leur uid et insère les 122
  manquants ; recalcule les commissions ; purge le rattachement en trop de
  Riotte ; acquitte les dépôts.
  *Contrôles* : six dépôts « acquitté » ; « Contrôle : tous les scrutins du
  dépôt sont en base » ; « 3839 mandat(s) existant(s) rattaché(s) » ;
  `SELECT COUNT(*) FROM scrutin WHERE legislature=17 AND uid LIKE 'VTANR%'` ≥
  9895 ; `SELECT COUNT(*) FROM mandat` = 3961.
- [ ] **4.** `app:import:prises-fonction --tout` — doit poser 0 date.
- [ ] **5. Comptes d'essai** : supprimer `redaction`, `editeur`, `guibert`
  (l'un partage son adresse avec un compte du legacy).
- [ ] **6. Export du legacy** : `bin/recuperation/exporter-legacy.sh` →
  `~/recuperation-legacy/<date>/` (34 fichiers, `MANIFESTE.tsv`).
- [ ] **7. Récupération** : `app:recuperation:legacy --dossier=… --verifier`,
  puis sans `--verifier`. Reprend comptes, décryptages et auteurs, newsletter,
  blog, professions de foi, amendements des 15e-16e, résumés IA et mots-clés,
  et en dernier les votes MODEM de la 15e (`--file`, jamais `--truncate`).
  *Contrôles* : chaque bilan tombe juste ; 72 comptes ; 1 145 abonnés ;
  vote_2751 de la 15e : 569 votes, 0 doublon sur (`depute_id`, `scrutin_id`,
  `vote_type`).
- [ ] **8. Municipales** : `php -d memory_limit=512M bin/console app:import:municipales`
  (~3 min, 813 Mo gardés dans `var/municipales/`, supprimables ensuite), puis
  `app:import:noms-communes`.
  *Contrôle* : 54 119 listes, 36 362 lignes de participation.
- [ ] **9. Calculs** : `app:calcul:statistiques-deputes --legislature=15`, puis
  `--legislature=17`, puis `app:calcul:classements` (~3 min).
  *Contrôle* : loyauté de Chenu à 95 % sur 1 041 votes.
- [ ] **10.** Copier `~/public_html/assets/data/professions/` (1,9 Go de PDF)
  dans le `public/` partagé de l'application.
- [ ] **11.** `app:cache:rechauffer` (~6 min).
- [ ] **12. Contrôles en ligne** : `vote_9178` (17e) en 200 ; `vote_2751` (15e)
  avec sa ligne MODEM et 568 votants ; Chenu « Rassemblement National · POUR ·
  REBELLE » sur `vote_8280` ; bloc municipales sur
  `/elections/resultats/bouches-du-rhone-13/ville_marseille` ; exposé et auteur
  sur `/votes/legislature-16/vote_119` ; connexion d'un compte repris.
- [ ] **13.** Rejouer la veille comparative (`../datan-veille`, une passe
  complète) : c'est elle qui avait relevé ces écarts.

## 2. Résumés d'amendements par IA

La chaîne du legacy échoue chaque heure depuis le 13/09/2026 (« credit balance
is too low ») ; la nôtre attend le même crédit. Rien n'est planifié d'ici là.

- [ ] **Recharger le crédit Anthropic**, puis poser `ANTHROPIC_API_KEY` dans
  `shared/.env.local`.
- [ ] **Première mise en service** : `app:ia:resumes-amendements:soumettre --simulation`,
  puis `--essai=5`, puis un lot de 20 ; lire `lot_ia.jetons_cache_lus` avant
  de planifier. Reste à vérifier avec une vraie clé : l'acceptation du schéma
  de sortie et des réglages en mode lot, le taux de lecture du cache, la
  qualité à effort bas.
- [ ] **Mots-clés** : `app:ia:tags:nettoyer` après la récupération — simulation
  à relire, puis `--appliquer` (4 124 libellés pour 6 509 amendements, 58 %
  employés une seule fois ; ~3 centimes).
- [ ] **Bascule des crons** (`CRON.md` §3) : couper les deux lignes du legacy,
  décommenter les deux nôtres (soumission toutes les 6 h par lots de 100,
  traitement toutes les heures), `bin/cron.sh installer`.
- [ ] Relire les résumés dans `/admin/amendements` : rien ne s'affiche au
  public avant (21 relus sur 6 509 repris).
- [ ] Plus tard : rapprochement des mots-clés entre catégories ; relever avec
  la rédaction l'exposé envoyé au modèle (300 caractères aujourd'hui).

## 3. Bascule de datan.fr (jour J)

- [ ] Refaire les étapes 6 et 7 de la mise en production **le plus tard
  possible** (la newsletter continue de s'inscrire au legacy), puis
  `app:recuperation:legacy --clore` et suppression de `~/recuperation-legacy/`.
- [ ] **Newsletter** : `MAILJET_API_KEY`/`SECRET` et `MJML_APP_ID`/`SECRET_KEY`
  dans le `.env.local` de production (clés du `.htaccess` du legacy) ;
  `app:newsletter:votes test` → lettre reçue à info@datan.fr ; retirer du
  crontab `newsletter votes send` et `newsletter update` du legacy, décommenter
  `app:newsletter:votes` dans `bin/crontab.prod` à la même heure,
  `bin/cron.sh installer` ; inscrire une adresse d'essai, vérifier bienvenue et
  listes chez Mailjet, puis `/newsletter/edit/<adresse>` et
  `/newsletter/delete/<adresse>`.
- [ ] Rebrancher **Matomo** (matomo.datan.fr, siteId 1), absent de la
  préproduction à dessein (`MATOMO_URL` vide). Décider si l'**iframe** des
  députés doit le porter : le legacy l'y charge, `IframeController` l'en a
  retiré — sans lui, les affichages chez les sites tiers ne sont plus comptés.
- [ ] **Cloudflare** : datan.fr est derrière (nuage orange), la préproduction
  non. Vérifier le mode SSL de la zone — en « Flexible », PHP reçoit du `http`,
  les canoniques sortent en `http://` et le cache réchauffé sous `https://` ne
  sert à personne : exiger « Full (strict) » — et décider si Symfony lit l'IP
  des visiteurs dans `CF-Connecting-IP` (`trusted_proxies`).
- [ ] Retirer le bloc « PRÉPRODUCTION » de `public/.htaccess`
  (`DEPLOIEMENT.md` §6) : sans cela, images et PDF de datan.fr porteraient
  `X-Robots-Tag: noindex`.
- [ ] Passage du compte o2switch en PHP 8.4 : vérifier le legacy le jour même.
- [ ] Vérifier que canonical et `og:url` basculent sur datan.fr (générés depuis
  l'hôte), et que les images `posts/inside/` des articles — référencées en
  absolu vers `https://datan.fr` dans 16 articles — se chargent bien des
  fichiers locaux.
- [ ] Rejouer la vérification des plans de site : chaque adresse annoncée → 200,
  en l'interrogeant réellement.
- [ ] `deploy.yml` **sur `master`** : ajouter `concurrency: { group: o2switch-datan }`.
  Sans cela, un déploiement du legacy peut retirer l'IP de notre runner de la
  liste blanche SSH en plein rsync.

## 4. Écarts avec datan.fr à instruire

Pour chacun : lire la règle du legacy (souvent `daily.php`), trancher parité ou
correction, **commenter le choix** dans le code, et le consigner dans
`DIFFERENCES.md` si c'est un écart voulu.

### Statistiques

- [ ] **Participation des députés, 89 % (legacy) contre 90 %**, et dénominateur
  du tableau « Tous les votes » : uniforme chez nous (8 411 pour un député
  présent toute la législature), variable au legacy (8 402 / 8 405 / 8 408).
  La règle est dans `votes_participation` (daily.php:2270-2354) : motions de
  censure exclues (nous aussi), borné au mandat principal, et un scrutin où le
  député est non-votant de droit vaut **NULL** — ni numérateur ni
  dénominateur ; une exception en dur, `PA721908` → NULL après le 2022-06-22.
  `CalculStatistiquesDeputesCommand` retire déjà les non-votants sur les
  solennels ; reste le tableau (`CalculClassementsCommand`). Cas de contrôle :
  Hamelet 8 402, Salmon 8 408. Les ±1 pt de cinq députés et le 89/90 devraient
  tomber ensemble.
- [ ] **« Votes par spécialisation »** : 35 % en production contre 32 % au
  legacy ; 463 pourcentages sur 574 diffèrent (Clémence Guetté 89 % → 25 %).
  Deux causes à séparer : notre dédoublonnage par commission dominante (le
  legacy double 9 députés, une ligne par commission — correction probable à
  assumer), et le périmètre des « votes liés à la commission », plus large chez
  nous (298 → 308, 1 422 → 1 602…) parce que le legacy scrape le lien scrutin ↔
  dossier, à couverture partielle.
- [ ] **Graphique de féminisation** : nous supprimons la barre 1997-02 et
  scindons 2022-27 en 2022-24 (37 %) / 2024-29 (38 %), ce qui inverse la phrase
  (« a légèrement baissé » → « augmenté »). Le libellé « 2022-27 » du legacy
  est faux depuis la dissolution : trancher.
- [ ] **Représentativité sociale** : LIOT 0.107 → 0,142 ; LFI-NFP 0.432 →
  0,446 ; sur la fiche DEM la catégorie à 0 % diffère (« ouvriers, 11 % »
  contre « personnes n'ayant jamais travaillé, 13 % »). Comparer nomenclature
  et calcul de l'indice.
- [ ] **Cohésion** : moyenne 0.927 → 0,925 ; DR 0.899 → 0,898 ; les onze autres
  groupes identiques — arrondi ou périmètre marginal.
- [ ] **Départage des ex æquo** (top 3 de participation à 100 %, carte « Vote
  le plus », fins de classement) : reproduire le tri secondaire du legacy s'il
  en a un, sinon en poser un déterministe et le commenter.
- [ ] **Avertissement Covid-19** (« Attention, à cause de la crise… ») retiré
  chez nous : probablement périmé, à décider puis consigner.

### Élections

- [ ] **« Les prochaines élections en France »** : section absente. Le texte du
  legacy est périmé ; la reconstruire avec un contenu juste.
- [ ] **Second tour d'Ambérieu-en-Bugey (législatives 2024)** : « pas de second
  tour » au legacy, résultats complets chez nous (Chavent 51,27 % / Pisani
  48,73 %). Chavent n'avait que 36,47 % au premier tour : nous avons sans doute
  raison ; vérifier à la source puis consigner.
- [ ] **Top 30 des plus grandes communes** : nous y plaçons Saint-Denis de La
  Réunion (20e), le legacy l'exclut. Retrouver son critère.
- [ ] **Candidats aux municipales sur la fiche DEM** : 21 au legacy, 18 chez
  nous. Hypothèse non vérifiée : `GroupeController::candidatsMunicipales` ne
  compte que les députés en exercice, le legacy tous les candidats rattachés
  au groupe (`deputes_last`).

### Fiches député, vote et groupe

- [ ] **Encart du scrutin 3684** : affiché au legacy même quand le député n'a
  pas voté (sans phrase), masqué chez nous. Non instruit.
- [ ] **Texte de présentation de la fiche DEM** : paragraphe enrichi absent du
  legacy, terminé par « .. ». Corriger la ponctuation ; décider si le texte
  reste (s'il vient de la rédaction, c'est le legacy qui est en retard).
- [ ] `DeputeController::communeAvecDe` écrit « de Hirson » ; `commune.nom_de`
  (rempli par `app:import:noms-communes`) donnerait « d'Hirson », comme le
  legacy.

### Gabarits et typographie

- [ ] **Séparateur décimal** : virgule chez nous (0,86 ; 4,4 %), point au legacy
  — systématique. La virgule est le bon usage : trancher une fois pour tout le
  site et consigner (incohérence actuelle : « 51.7 ans » garde le point des
  deux côtés).
- [ ] **Campagne de dons (`#campaign`)** : absente de `/votes` chez nous
  (présente au legacy), présente sur les pages mois (absente au legacy),
  placée autrement sur les pages commune. Fixer la liste des pages porteuses.
- [ ] **h2 de `/votes`** reformulé (« Les derniers votes de l'Assemblée
  nationale ») : à garder, mais le commentaire qui dit pourquoi manque.
- [ ] Détails de CSS : pictogramme d'attente des photos de députés (cercle vide
  chez nous, « personne » au legacy) ; position du pourcentage gagnant et
  soulignement du lien dans « Son élection » ; graisse de l'icône « Proximité
  avec son groupe » ; ordre des fenêtres modales du pied de page.
- [ ] `alt` des vignettes du blog : « Image post 15 » — l'identifiant diffère
  d'une base à l'autre. Mettre le titre de l'article.
- [ ] **Mentions légales, à relire par la rédaction** : le texte annonce encore
  des « cookies de mesure d'audience (Google) » et un compteur « soumis à
  consentement », alors que GTM et Tarte au citron sont partis le 10/09/2026.
  Texte légal : à signaler, pas à réécrire de notre chef.

## 5. Code à reprendre

- [ ] **`nomin_principale` subsiste** là où il donne encore le bon résultat
  (dernier rattachement ouvert : `DeputeListController`, `ElectionController`,
  `OutilsController`, `CalculClassementsCommand` ; comptages en `DISTINCT` de
  `GroupeController` et `MoyennesAssemblee`). Juste aujourd'hui, fondé sur le
  contresens que décrit `CLAUDE.md` : à basculer sur
  `GroupeAuScrutin::APPARTENANCE`.
- [ ] `CalculClassementsCommand`, repli sur `mandat` pour un député sans
  `fonction_groupe` : ne garde que le premier mandat. Sans effet en 17e.
- [ ] **Contrôle dépôt ↔ base pour les amendements**, sur le modèle de celui des
  scrutins : 15 amendements avaient disparu sans alerte.
- [ ] `doctrine.yaml` : poser `wait_timeout` à l'ouverture de la connexion, pour
  les commandes lancées hors de `app:sync:quotidien`.
- [ ] Supprimer `ImportAnnexesCommand` (remplacée par `app:import:amendements-clos`)
  et `app:import:votes-cles` (sans objet depuis le 29/07/2026).
- [ ] Tester une restauration de `exporter-hors-sync.sh` sur une base MariaDB
  jetable : elle ne l'a été que sous MySQL 8.

## 6. Hébergement

- [ ] **OPcache absent** de la configuration PHP 8.4 du sélecteur o2switch :
  ~250 ms de coût fixe par page hors cache (`DEPLOIEMENT.md`, « OPcache »).
- [ ] Faire confirmer par o2switch l'origine des SIGHUP vers 6 h : corrélés à la
  remise de configuration PHP (`php-selecteur.log`), non prouvés.
