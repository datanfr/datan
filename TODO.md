# TODO — ce qu'il reste à faire

Seulement l'ouvert. Ce qui est fait vit dans l'historique git ; les écarts
voulus avec datan.fr, à ne pas « corriger », sont dans `DIFFERENCES.md`.

Rappel de méthode : datan.fr répond 429 en rafale — une URL à la fois, 2 s
d'écart — et garde ses fiches en cache jusqu'à trois jours : un écart de
compteur peut n'être qu'un écart de fraîcheur. La règle reste celle de
`CLAUDE.md` : parité sur ce qui est un choix, correction sur ce qui est un
défaut, et la réponse est dans le code du legacy.

---

## 1. Suites de la mise en production du 2026-11-16

Déployé et rejoué sur symfony.datan.fr le 16/11/2026 : synchronisation
complète, récupération du legacy, municipales, recalculs, professions de foi.
Reste :

- [ ] **Comptes d'essai** `redaction` (admin), `editeur`, `guibert` : à
  supprimer avant la bascule, ou à garder sciemment. Aucun n'est référencé
  (ni décryptage, ni newsletter) et aucun n'existe au legacy.
- [ ] **Rejouer la veille comparative** (`../datan-veille`, une passe
  complète) : c'est elle qui avait relevé les écarts corrigés.
- [ ] Vérifier la synchronisation du lendemain dans
  `shared/var/log/sync-quotidien.log` : première exécution planifiée à 6 h 20,
  avec la ligne `BILAN` et l'acquittement des dépôts.
- [ ] Sur le serveur, à supprimer quand elles n'ont plus d'intérêt : la
  sauvegarde `shared/sauvegardes/avant-corrections-20261116-0822.sql.gz` et
  les journaux `shared/var/log/reparation-20261116-etape*.log`.

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

- [ ] Refaire la récupération **le plus tard possible** (la newsletter continue
  de s'inscrire au legacy) : `bin/recuperation/exporter-legacy.sh`, puis
  `app:recuperation:legacy --dossier=… --verifier` et la même sans
  `--verifier` (6 min le 16/11) ; puis `app:recuperation:legacy --clore` et
  suppression de `~/recuperation-legacy/`. Sans synchronisation en cours.
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
- [ ] **Cloudflare et le cache.** Les fichiers de `/assets/` et `/build/`
  s'annoncent immuables un an : Cloudflare les gardera en bordure sans rien
  régler. Le HTML, lui, n'y est pas mis en cache aujourd'hui
  (`cf-cache-status: DYNAMIC` sur datan.fr) et chaque page continue d'arriver
  jusqu'à nous. Si l'on décide de le mettre en cache en bordure, trois choses
  avant d'activer la règle : la faire contourner par le cookie de session
  (`PHPSESSID`), brancher la purge de Cloudflare par API dans
  `RechauffeurCache::purger()` — sans quoi une publication n'apparaît plus —,
  et vérifier que son « Browser Cache TTL » respecte l'origine, qui ne donne
  aucune durée au navigateur pour une page.
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
- [ ] **Lien de groupe faux sur `/deputes/…/legislature-N`** : la carte de
  profil garde le `groupe_id` *courant* pour bâtir le lien
  (`depute/_carte_profil.html.twig:32`) alors que `legislature.html.twig` ne
  remplace que libellé, sigle et couleur. Pour un membre actuel de l'UDR,
  l'adresse du groupe d'époque est fausse. Relevé le 16/11/2026, non corrigé.
- [ ] **Sigle de groupe d'une explication de vote** : `VoteController` affiche
  le groupe *actuel* de l'auteur (`d.groupe_id`), vide s'il ne siège plus, et
  non son groupe au jour du scrutin. À comparer au site d'origine.
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
  ~250 ms de coût fixe par requête (`DEPLOIEMENT.md`, « OPcache »). Mesuré le
  16/11/2026 : une page servie **depuis le cache** sort en 325 à 353 ms, pour
  20 ms visés. C'est le premier levier de vitesse du site — le cache HTTP
  n'économise que le rendu, pas le démarrage de PHP.
- [ ] **Poids du magasin des pages figées** (`var/cache/prod/http_cache_fige`) :
  il ne se vide plus chaque matin. Une page de commune pèse ~95 Ko ; si les
  70 000 pages de communes et de résultats sont toutes visitées entre deux
  purges, il atteint ~7 Go. La partition avait 402 Go libres le 16/11/2026 et
  aucun quota n'est visible, mais c'est à surveiller les premières semaines
  (`du -sh`), comme la fréquence réelle des purges dans `sync-quotidien.log`
  (« pages figées conservées » / « pages figées comprises »).
- [ ] Faire confirmer par o2switch l'origine des SIGHUP vers 6 h : corrélés à la
  remise de configuration PHP (`php-selecteur.log`), non prouvés.
