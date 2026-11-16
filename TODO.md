# TODO — plan de correction issu de la comparaison datan.fr ↔ datan.remikel.fr (2026-08-08)

Campagne de comparaison complète : diff des 14 sitemaps (~40 000 URL), comparaison
HTML de 10 sections, captures pleine page de 6 pages clés, mesures de temps de
réponse. Verdict : portage très fidèle, performance équivalente. Reste ce qui suit,
classé par priorité. Chaque point dit **quoi**, **où chercher**, et **comment vérifier**.

Rappel de méthode : datan.fr est derrière une protection anti-rafale (429 o2switch) —
toute vérification côté legacy se rejoue **une URL à la fois**. Une section « vide »
sur une capture headless peut n'être qu'un artefact de `loading="lazy"` : vérifier le
HTML avant de conclure.

---

## P0 — Contrat d'URL (bloquant : des URL indexées rendent 404)

- [x] **Commune « Faux » (Dordogne) importée sous le slug « 0 ».** — *Fait le
  2026-08-08.* La corruption venait du **dump** (les deux communes « Faux »,
  08165 et 24177, y portent `commune_nom = '0'` : « Faux » est le libellé
  français du booléen FALSE, converti par un aller-retour tableur ; la vraie
  prod sert bien `ville_faux`). Corrigé aux trois niveaux : requête d'export
  auto-réparante via `cities.nom_standard` (docblock d'`ImportCommunesCommand`),
  garde dans l'import qui refuse toute ligne sans lettre (avec bilan chiffré),
  données locales réparées, TSV régénéré et import rejoué. Vérifié : sitemap
  local annonce `ville_faux`, plus aucun `ville_0`, 0 commune corrompue en base.
  **Reste : redéployer + rejouer l'import sur le staging.**

- [x] **Slugs de communes à article parenthésé.** — *Fait le 2026-08-08, sans
  301 : alignement des données sur les adresses que la prod sert.* Recensement
  complet des 24 communes à slug parenthésé : seules **Étoile (L')** (39217 →
  `etoile`) et **Nonières (Les)** (07165 → `nonieres`) divergent — la prod a
  nettoyé ces deux slugs après la prise de notre dump (vérifié sur les pages
  département de datan.fr, qui font foi). `CASE` posé dans la requête d'export
  (docblock d'`ImportCommunesCommand`), données alignées, sitemap vérifié.
  Assions, Ollières-sur-Eyrieux et Bonvillers gardent leurs parenthèses (la prod
  les lie ainsi mais les refuse en 400 — aucun contrat à honorer) ; les 19
  autres ne sont liées nulle part sur datan.fr.
  **Reste : redéployer + rejouer l'import sur le staging.**

## P1 — Fiches vote : title et casse du h1 (18 311 pages, SEO)

- [x] **Reprendre le gabarit `<title>` du legacy.** — *Fait le 2026-08-08.*
  Nouvelle classe `App\Scrutin\TitreMeta` : portage du CASE SQL de
  `Votes_model::get_individual_vote()` (Vote final / Motion de renvoi /
  Amendement n°X / Article n°X + titre du dossier), avec extraction des numéros
  d'amendement et d'article depuis le libellé comme `daily.php:1788-1810` (les
  colonnes `amdt`/`article` n'ont pas été portées en base). La meta description
  reprend aussi le format legacy (« Découvrez le vote des députés sur le
  scrutin : … », variante explication de vote). Vérifié identique au caractère
  près sur : vote_1 L16 (Motion de censure), vote_100 L15 (amendement sans
  dossier), vote_997 L17 (Vote final), vote_8426 (Amendement n°1),
  vote_8415 (Article n°13), vote_c1 L16 (Congrès).
- [x] **Retirer le `|capitalize` du h1.** — *Fait le 2026-08-08.* Filtre Twig
  `ucfirst` ajouté à `DatanExtension` (première lettre seulement, multi-octets),
  appliqué au h1 et au « Type de vote » de l'encart Infos. « La NUPES »,
  « M. Aviragnet », « Élisabeth Borne » gardent leurs majuscules ; vote_997
  (déjà tout en minuscules) inchangé.

## P1 — Données de production en retard ou non portées

**Source découverte le 2026-08-08** : le jeu public
`datan.fr/assets/dataset_backup/general/latest.sql` (195 Mo, daté du 31 juillet
2026) est bien plus complet que ne le disait `CLAUDE.md` — il porte
`cities_mayors` en entier (34 874 lignes), les 240 décryptages publiés et 47
explications. Chargé dans le conteneur (`datan_backup`), il a servi à tous les
rattrapages ci-dessous. Rester prudent : c'est un instantané au 31 juillet et
les comptes y sont anonymisés — **au déploiement, tout se rejoue contre la
vraie base**.

- [x] **Explications de vote rattrapées.** — *Fait le 2026-08-08.* 47 importées
  (42 publiées, contre 40 avant). Export TSV régénéré depuis le jeu public.
  Reste l'écart de fraîcheur : la prod du 8 août en a de plus récentes que
  l'instantané du 31 juillet, **à rejouer au déploiement**.
- [x] **Décryptages rattrapés.** — *Fait le 2026-08-08.* Les 2 manquants
  (acétamipride 8427, aide à mourir 8280) sont importés : 252 en base, **240
  publiés — le compte exact du legacy**. Vérifié : `/votes/decryptes` affiche
  « 62 votes… » et 12 vignettes Agriculture, identiques à datan.fr ; l'accueil
  et les carrousels ressortent vote_8427 et vote_8280.
  *Méthode* : `ImportDecryptagesCommand` attend une connexion PDO et le
  conteneur refuse les connexions hors localhost ; les trois tables source
  (`votes_datan`, `fields`, `readings`) ont donc été chargées temporairement
  dans `datan_symfony`, l'import lancé dessus, puis les tables supprimées
  (schéma revérifié « in sync »).
- [x] **Table des maires portée.** — *Fait le 2026-08-08.* Elle n'était pas
  perdue, seulement vide dans la copie de travail. Migration
  `Version20260808090000` (3 colonnes sur `commune`), import via
  `app:import:communes --maires` (requête d'export au docblock), **34 865
  communes avec maire**. Affichage rétabli aux deux endroits : « Le maire
  d'Ajaccio est Stéphane Sbraggia. » sur les fiches de ville et la ligne
  « 🏛️ Maire » de l'encadré des pages résultats. L'élision est corrigée
  (`ville.de`) par cohérence avec le reste de la page. La correction manuelle du
  legacy (Berre-l'Étang, SALVO → Doriol) n'est pas portée : son référentiel a
  été mis à jour depuis et elle ne se déclenche plus.
- [x] **Images in-article du blog récupérées.** — *Fait le 2026-08-08.*
  17 fichiers rapatriés sous `public/assets/imgs/posts/inside/`, sous-dossiers
  compris (`2025_classement/`, `interview_lebras/`) — recensés en cherchant les
  URL dans le corps des articles, pas seulement celles d'un article. Tous
  répondent 200 en local.
  - [ ] **Reste** : les corps d'articles référencent ces images en **absolu**
    vers `https://datan.fr`. Tant que le domaine ne bascule pas, elles se
    chargent depuis la prod ; à la bascule, elles se chargeront des fichiers
    locaux. Vérifier ce jour-là. (16 articles portent des liens absolus
    `datan.fr`, images et liens internes confondus.)
- [x] **Section « Ses professions de foi »** — *instruit le 2026-08-08 : déjà
  entièrement codée* (gabarit `depute/_professions_foi.html.twig`, contrôleur,
  commande `app:import:professions-foi` avec export au docblock). Elle
  n'apparaît pas car `profession_foi` est vide dans la copie de travail ET dans
  le jeu public (réduit, comme `users_mp`) — vérifié dans `datan_backup`.
  - [ ] **Au déploiement** : rejouer l'import contre la vraie base et copier le
    répertoire `assets/data/professions/` du serveur (les PDF n'ont pas
    d'index public, seule la table sait lesquels existent).

## P1 — Statistiques : écarts de calcul à instruire un par un

Pour chacun : comprendre la règle du legacy (la réponse est dans son code, souvent
`daily.php`), puis trancher parité/correction et **commenter le choix** dans le code.

- [ ] **Participation moyenne 89 % (legacy) vs 90 % (staging)** — même total de
  8 434 scrutins annoncé des deux côtés, donc écart de calcul, pas de fraîcheur.
  Lié au point suivant.
- [ ] **Dénominateur « nombre de votes » du tableau « Tous les votes »** : uniforme
  à 8 411 côté staging pour un député présent toute la législature, variable côté
  legacy (8 402 / 8 405 / 8 408 selon le député). 568 lignes sur 576 diffèrent,
  5 pourcentages bougent de ±1 pt.
  *Instruit le 2026-08-08 — la règle legacy est dans `votes_participation`
  (daily.php:2270-2354)* :
  1. périmètre `codeTypeVote != "MOC"` (motions de censure exclues) — nous le
     faisons aussi : 8 434 − 23 MOC = notre 8 411 ;
  2. borné au `mandat_principal` du député (`datePriseFonction`…`dateFin`) ;
  3. un scrutin où le député est `nv` (non-votant de droit) vaut **NULL** — ni
     numérateur ni dénominateur : c'est ça, les 8 402/8 405/8 408 par député
     (8 411 moins ses scrutins présidés/au Gouvernement). Notre
     `CalculStatistiquesDeputesCommand` retire déjà les non-votants sur les
     solennels ; reste à réconcilier le tableau « Tous les votes »
     (`CalculClassementsCommand` ?) qui garde un dénominateur uniforme ;
  4. une exception codée en dur : `PA721908` → NULL après le 2022-06-22.
  À réconcilier député par député (prendre Hamelet 8 402 et Salmon 8 408 comme
  cas de contrôle) avant de toucher au code — l'écart de ±1 pt sur 5 députés et
  le 89/90 % moyen devraient tomber avec le même correctif.
- [ ] **Tableau « Votes par spécialisation »** : legacy 583 lignes (9 députés en
  double — une ligne par commission —, Braun-Pivet absente), staging 575 (dédoublonné,
  Braun-Pivet réintégrée) ; 463 pourcentages sur 574 diffèrent, certains massivement
  (Clémence Guetté 89 % → 25 %). Deux causes à séparer :
  1. le dédoublonnage (commission dominante) — probablement une correction à assumer ;
  2. le périmètre « votes liés à la commission », systématiquement plus large côté
     staging (298→308, 1422→1602, 1774→2369…) : le legacy scrape le lien
     scrutin↔dossier sur assemblee-nationale.fr (couverture partielle). Documenter la
     source de chaque côté et choisir.
- [ ] **Graphique « évolution du taux de députées femmes »** : le staging supprime la
  barre 1997-02 et scinde 2022-27 en 2022-24 (37 %) / 2024-29 (38 %), ce qui inverse
  la phrase dynamique (« a légèrement baissé » → « augmenté »). Le libellé legacy
  « 2022-27 » est faux depuis la dissolution, mais l'écart visuel est important :
  trancher, et si le découpage staging est gardé, le commenter comme correction.
- [ ] **Représentativité sociale** : LIOT 0.107 → 0,142 ; LFI-NFP 0.432 → 0,446 ;
  et sur la fiche DEM la catégorie à 0 % diffère (« ouvriers, 11 % » vs « personnes
  n'ayant jamais travaillé, 13 % »). Comparer la nomenclature des catégories et le
  calcul de l'indice.
- [ ] **Cohésion** : moyenne 0.927 → 0,925 ; DR 0.899 → 0,898 (les 11 autres groupes
  strictement identiques — probable écart d'arrondi ou de périmètre marginal).
- [ ] **Avertissement Covid-19** (« Attention, à cause de la crise de la Covid-19… »)
  supprimé côté staging : probablement obsolète, mais décider et documenter.
- [ ] **Départage des ex æquo** (top 3 participation à 100 %, carte « Vote le plus »,
  fins de classement) : le legacy a-t-il un tri secondaire reproductible ? Si oui le
  reproduire, sinon en poser un déterministe et le commenter.

## P1 — Page /elections et pages résultats

- [x] **Bug de gabarit des cartes d'élection.** — *Fait le 2026-08-08.* Deux
  causes dans `ElectionRepository` : `SUM(c.elu = 1)` rendait NULL quand `elu`
  n'est renseigné nulle part (présidentielle, municipales) → `COALESCE(…, 0)`
  comme le `SUM(CASE … ELSE 0)` du legacy ; et l'état était déduit des dates
  alors que le legacy le **fige à la main** par élection
  (`get_election_state()`) — les municipales 2026, passées mais non dépouillées,
  basculaient à tort sur le décompte d'élus. État figé porté (1-6 → achevé,
  7 → attendu, défaut attendu). Vérifié : « 337 députés candidats »
  (municipales), « 0 député élu » (présidentielle), 1/422/292/47 élus, pied
  vide des départementales — tout identique au legacy.
- [ ] **Section « Les prochaines élections en France » absente** du staging.
  Le texte legacy est périmé (dates de mars 2026 au futur) : reconstruire la section
  avec un contenu juste plutôt que la laisser tomber.
- [ ] **Second tour d'Ambérieu-en-Bugey (législatives 2024)** : le legacy affiche
  « pas de second tour », le staging des résultats complets (Chavent 51,27 % /
  Pisani 48,73 %). Chavent n'avait que 36,47 % au T1 : le staging semble avoir
  raison. Vérifier contre la source, puis commenter comme correction d'un défaut de
  données du legacy (ou corriger si c'est l'inverse).
- [ ] **Top 30 des plus grandes communes** : le staging insère Saint-Denis de La
  Réunion (20e), le legacy l'exclut. Retrouver le critère legacy (exclusion
  outre-mer ? population différente ?) et s'aligner ou assumer.
- [x] **Casse des noms de candidats** — *fait le 2026-08-08.* La page résultats
  rend le nom brut, donc en capitales (« Marc CHAVENT »), comme
  `results_city.php` ; la fiche commune garde la casse mixte
  (`ucfirst(mb_strtolower())`), qui est aussi ce que fait le legacy à cet
  endroit-là — les deux pages du site ne suivent pas la même règle, nous non
  plus désormais.
- [x] **Habillage municipales 2026 des pages résultats** — *fait le 2026-11-16* :
  les municipales sont portées (cf. « Corrections du 2026-11-16 »).

## P2 — Sitemaps — *fait le 2026-08-08*

- [x] **Les 7 pages fixes sont annoncées** (`/a-propos`, `/faq`,
  `/mentions-legales`, `/soutenir`, `/parrainages-2022`,
  `/outils/coalition-simulateur`, `/blog`) — et `/blog` a quitté le plan des
  catégories pour celui de structure, comme sur le site (3 URL de rubriques,
  à l'identique).
- [x] **Mois zéro-paddés** dans le plan (`2026/07`), et **canonical** : les deux
  graphies d'une page mois pointent désormais vers la forme zéro-paddée
  (variable `canonical` posée par `vote/all.html.twig`, reprise par `base`).
  Le site a le défaut du double self-canonical ; on ne le reproduit pas.
- [x] **`ville_0` a disparu du sitemap** — vérifié après la correction P0
  (`ville_faux`, `ville_etoile`, `ville_nonieres` annoncées).
- [ ] Extensions assumées du sitemap staging (groupes NI, `/commissions`,
  `/legislature-N`, inactifs L14-16, `/votes/all` retirés des inactifs) : rien à
  faire, mais vérifier par échantillon que tout ce qui est annoncé répond 200.

## P2 — Lot de gabarits — *fait le 2026-08-08*

- [x] **Boutons « Voir les derniers votes » / « Tous les votes »** de l'accueil :
  repointés sur `/votes` (le legacy) au lieu de `/votes/legislature-17`.
- [x] **Meta description des archives mensuelles** : les trois formulations du
  legacy sont portées (mois, année, législature) — « en juin 2024 » revient, et
  les variantes courtes ne mentionnent plus la participation, comme chez lui.
- [x] **Tooltip de l'hémicycle** : « Députés non inscrits (NI) », via le
  `CASE WHEN libelle = 'Non inscrit'` que le legacy applique dans tout son
  `Groupes_model`.
- [x] **Camembert de la fiche vote** : étiquette « 113 » supprimée
  (`datalabels: {display: false}`, que le legacy pose explicitement) et jaune
  aligné sur le sien (#FFBA49, distinct du #FFAD29 du bandeau).
- [x] **Boutons « Le dossier » / « L'amendement »** : icône de lien externe
  rétablie, et liens passés par `url_obf` comme le legacy — ces sorties vers
  assemblee-nationale.fr étaient exposées en clair aux robots.
  Variante mobile du bloc « En savoir plus » ajoutée (elle manquait).
- [x] **Bulle d'aide « ? »** du titre « Les coalitions les plus fréquentes »
  rétablie (le legacy y réutilise le popover du taux de proximité).
- [x] **Badges de coalition** rangés par effectif décroissant (RN, EPR, DR,
  DEM…) et non alphabétiquement — c'est le `uasort` de `format_coalitions()`.
  L'effectif se compte sur `fonction_groupe`, valable aussi pour un groupe
  dissous.
- [x] **« Polynésie Française » → « Polynésie française »** : l'Assemblée
  surcapitalise l'adjectif, la table `departement` du site a la bonne graphie —
  exception posée dans l'import, qui préfère sinon l'orthographe de l'Assemblée.
- [x] **Ordre des logos de « La position des groupes »** : conservé par effectif
  décroissant, **divergence assumée et commentée** — la requête du legacy n'a
  aucun `ORDER BY` et son ordre est celui, arbitraire, des lignes de sa table
  `organes` (vérifié). Un accident de stockage ne se reproduit pas.

## P2 — Typographie et casse transverses

- [ ] **Séparateur décimal** : le staging affiche la virgule (0,86 ; 4,4 %) là où le
  legacy affiche le point (0.86 ; 4.4 %) — systématique (statistiques, cohésion,
  évolutions de population). La virgule est le bon usage français mais s'écarte de la
  parité : trancher une fois pour tout le site et commenter. (Incohérence actuelle :
  « 51.7 ans » garde le point des deux côtés.) Tranché sur la fiche commune par
  le legacy lui-même le 06/09/2026 : évolution de population à l'entier, « 4% »
  (cf. § Rattrapage legacy 2026-10-21).
- [x] Capitalisations gardées (« Val-d'Oise », « Côtes-d'Armor », « 2A »/« 2B ») :
  corrections assumées du legacy — commentées dans `ImportCommunesCommand`.

## P2 — Divers pages et gabarits

- [ ] **Section campagne de dons (`#campaign`)** : inclusion incohérente — absente de
  `/votes` côté staging (présente côté legacy), présente sur les pages mois côté
  staging (absente côté legacy), position différente sur les pages commune. Décider
  la liste des pages porteuses et l'emplacement, puis harmoniser.
- [ ] **h2 de `/votes`** : « Derniers votes non-decryptés de l'Assemblée nationale »
  (legacy, inexact et coquillé) vs « Les derniers votes de l'Assemblée nationale »
  (staging). La reformulation est défendable : garder, mais commenter pourquoi.
- [ ] **Placeholder des photos de députés** pendant le chargement : pictogramme
  « personne » (legacy) vs cercle dégradé vide (staging). Cosmétique, visible sur
  les grilles.
- [ ] **Position du % gagnant** dans l'encadré « Son élection » (fiche député) :
  aligné différemment ; et le lien « Consultez les résultats complets » n'est plus
  souligné. Deux détails de CSS à reprendre.
- [ ] **Icône « Proximité avec son groupe »** (poignée de main) : graisse différente.
- [x] **Liste « Les autres députés RN »** — *fait le 2026-08-08.* Le critère
  legacy est `deputes_all.groupeId` : le rattachement **le plus récent** de la
  législature (règle de CLAUDE.md), que le député siège encore ou non — c'est
  pour ça que Barthès et Bordes, partis de l'Assemblée, y figurent. Notre
  filtre « mandat ouvert » les excluait. Requête réécrite sur
  `fonction_groupe` ; liste vérifiée **identique lien à lien** à datan.fr sur
  la fiche Buisson.
- [x] **« Il vote rarement avec »** — *fait le 2026-08-08.* Le départage de la
  PHRASE est déterministe chez le legacy : son `array_multisort` compare les
  lignes entières et retombe sur le libellé en ordre d'octets (« Écologiste »,
  É = 0xC3 0x89, pèse plus que tout l'ASCII) — d'où ECOS « moins proche » de
  Buisson à 19 % ex æquo. Reproduit dans `ComportementDepute` ; vérifié
  identique. Les BARRES, elles, sortent du résultat SQL brut du legacy (ordre
  physique sur les ex æquo, accident non reproductible) : notre départage par
  sigle reste, commenté.
- [ ] **Fiche groupe DEM, texte de présentation** : le staging a un paragraphe
  enrichi absent du legacy, terminé par un « .. » (double point). Corriger la
  ponctuation ; décider si le texte enrichi reste (il vient probablement de la base
  de rédaction — dans ce cas c'est le legacy qui est en retard).
- [ ] **Candidats municipales fiche DEM** : « 21 députés candidats » (legacy) vs
  « 18 » (staging). Même famille que les données municipales non importées :
  vérifier la source du compteur.

## P2 — Performance et SEO techniques

- [x] **`<link rel=preload>` des images de couverture rétablis** — *fait le
  2026-08-08.* Bloc `preloads` dans `base.html.twig` + partiel
  `partials/preload_couverture.html.twig`, posé sur les 17 gabarits
  correspondant aux déclarations du site (relevées méthode par méthode dans ses
  neuf contrôleurs : City, Departement×2, Deputes::votes, Groupes×4,
  Pages::view, Parties, Posts×2, Quiz×2, Votes::all+individual). Vérifié : 3
  preloads sur chaque page testée.
- [ ] **Alt des vignettes blog** : « Image post 15 » vs « Image post 26 » — l'ID
  auto-incrémenté diffère entre bases. Sans gravité, mais un alt parlant (titre de
  l'article) réglerait la question mieux que la parité.
- [ ] **Troncature meta description des articles** : coupe un mot plus tôt que le
  legacy (l'entité `&nbsp;` compte 6 caractères chez lui). À ignorer sauf si l'on
  vise l'octet près.

## Corrections issues de la veille comparative (2026-10-26)

Relevées par la veille datan.fr ↔ symfony.datan.fr, corrigées et vérifiées
contre datan.fr sur la base locale :

- [x] **Groupe et loyauté au jour du scrutin** (`App\Depute\GroupeAuScrutin`) :
  fiche vote et page « tous les votes » confrontaient chaque vote au groupe
  ACTUEL — colonnes vides sur les 14e-16e, et rebelle à tort qui a changé de
  groupe (C. Le Nabour, EPR → HOR, vote 8411). Vérifié : Salmon 2 201 loyaux
  hors 17e, Le Nabour 3 743 + 2 235 en 17e, identiques au legacy.
- [x] **Vote du Congrès (IVG, `vote_c1`)** : l'import écartait le dossier `CG/`.
  Lu désormais, blocs du Sénat écartés en entier : 557 députés, le compte du
  legacy (557 + 345 sénateurs = 902). Les trois formulations Congrès de la fiche
  (« députés et sénateurs… ») rétablies.
  - [x] **Au déploiement** — *fait le 2026-10-26 sur symfony.datan.fr* : clone
    partiel du seul `CG/` de Scrutins_XVI (`git sparse-checkout set CG`),
    `app:import:scrutins --depot=<ce clone> --tout` (557 votes, 345 sénateurs
    écartés), `app:calcul:statistiques-deputes --legislature=16`,
    `app:cache:rechauffer`. Le reste de la 16e, déjà en base, n'avait pas à
    être réimporté. Vérifié en ligne : vote_c1 identique au legacy ligne à
    ligne (557, 505 loyaux).
- [x] **Fiche d'un ancien député** : le bloc « Son comportement politique »
  suit sa dernière législature (Pietrasanta : « (14e législature) », 95 % /
  100 %, identique au legacy) au lieu de disparaître.
- [x] **Mandat régional** des participations électorales : nom de la région
  (`App\Territoire\Region`) au lieu du code INSEE (« 32 » → « Hauts-de-France »).
- [x] **Fiche député en 500 en dev** — *fait le 2026-11-16* : `|default(null)`
  sur les quatre cartes de `_statistiques.html.twig`, et sur `contact.mail_an`
  quand le député n'a pas de contact (`_bio.html.twig`).

Différences assumées nées de ces corrections (à ne pas « corriger ») :
- Loyauté **non jugée** quand le groupe n'a pas de position majoritaire (`nv` :
  égalité, ou personne n'a voté). Le legacy rend « rebelle », ou « loyal » sur
  ses données 15e importées autrement (Ménard, NI, vote_813).
- Membres du Gouvernement sur une motion de censure : « NON-VOTANT » sans
  loyauté, là où le legacy écrit « REBELLE » sans position (vote_1, 16e).
- « REBELLEE » (accord fautif du legacy) → « REBELLE ».

## Corrections du 2026-11-16 (suite de la veille comparative)

Instruites par écart, contre le code du legacy et les deux bases de production
(lecture seule). Règle suivie : un écart dû à un meilleur calcul du port se
garde et se documente, un vrai défaut se corrige.

### Synchronisation
- [x] **Trou des scrutins 9156-9200** : chaque moisson réécrivait
  `.datan-modifies` ; le 29/10 l'import a été tué après la moisson, celle du 30
  a remplacé le delta. Le delta se calcule désormais depuis `.datan-acquitte`
  (dernière chaîne entièrement réussie) et `app:import:scrutins` compare le
  dépôt à la base après chaque passe. Reproduit avant/après sur un dépôt jouet.
- [x] **Chaîne fragile** (11 échecs sur 20 exécutions du 27/10 au 15/11 :
  « MySQL server has gone away », Hangup) : `wait_timeout` relevé en session,
  seconde tentative sur connexion perdue, SIGHUP ignoré, purge du cache HTTP
  même après un échec, verrou, cron à 6 h 20 avec ligne `BILAN`.
- [x] **Doublons de votes au rejeu** : `ImportVotesCommand` posait
  `unique_checks = 0`, qui désactive l'index unique sous InnoDB.
- [x] **Auteur de l'amendement** : `app:import:auteurs-amendements` et
  `app:import:commissions` entrent dans la chaîne (24 795 amendements de la 17e
  sans auteur en production, 1 297 scrutins sans la carte).
- [x] **`nomin_principale` dans l'import des scrutins** (`groupeDeduit`,
  `groupesParElimination`) : remplacé par l'appartenance, effectifs comptés en
  `DISTINCT`.
- [ ] **vote_2751 et six autres scrutins de la 15e** (2418, 2744, 2752, 2761,
  2762, 2766) : la source a perdu le bloc MODEM. 234 votes et 7 ventilations à
  reprendre de la base du legacy (requêtes dans les docblocks d'
  `ImportVotesCommand` et d'`ImportVoteGroupesCommand`) — cf. mise en production.
- [ ] Six scrutins de la 15e (1027-1029, 2814, 2816, 3778) incomplets à la
  source **et** au legacy : rien à reprendre, ventilation partielle assumée.
- [ ] 15 amendements (`AMANR5L17PO59048B3210P2D1…`) perdus par un écrasement de
  delta antérieur : la synchronisation complète de la mise en production les
  ramène. Ajouter pour les amendements le contrôle dépôt/base des scrutins.
- [ ] Faire confirmer par o2switch l'origine des SIGHUP vers 6 h (corrélés à la
  remise de configuration PHP dans `php-selecteur.log`, non prouvés).
- [ ] `doctrine.yaml` : poser `wait_timeout` à l'ouverture de connexion, pour
  les commandes lancées hors de la chaîne.

### Fiches député et vote
- [x] **Présidents de groupe sans groupe ni loyauté** : `GroupeAuScrutin` filtre
  les qualités d'appartenance et non plus `nomin_principale` (cf. CLAUDE.md).
  vote_8280 : 561 lignes identiques à datan.fr ; vote_504 : 146. 2 939 votes de
  la 17e retrouvent leur groupe.
- [x] **Loyauté des présidents** (`CalculStatistiquesDeputesCommand`,
  `CalculClassementsCommand::loyauteDeputes`) : Chenu 95 % sur 1 041 votes au
  lieu de 100 % sur 14, comme `class_loyaute` ; Naegelen, Peu, Marcangeli de
  même. Aucun autre député ne bouge.
- [x] **Votes décryptés** : les mises au point comptaient comme des votes
  (Autain 85 au lieu de 82 ; 299 lignes en trop sur 241 fiches).
- [x] **Carrousel des votes décryptés** d'une fiche vote : 7 votes et la carte
  « VOIR TOUS », comme `get_last_votes_datan(7)`.
- [x] **« … en est le président »** et la phrase des apparentés, portées sur la
  fiche député (11 présidents, 44 apparentés).
- [x] **Commission** : mandat COMPER ouvert (cf. CLAUDE.md) — Braun-Pivet en
  Finances. Accord avec datan.fr sur 544 députés sur 574, contre 477.
- [x] **Rattachement requalifié** (Riotte, membre puis apparenté d'Horizons) :
  l'ancienne ligne restait ouverte ; `ImportActeursCommand::purgeRequalifies()`.
- [x] **Clé des mandats** : `mandat.uid` remplace (député, législature, début),
  qui écrasait le premier mandat d'un ministre revenu siéger. 3 839 → 3 961
  mandats, ancienneté moyenne 6,52 ans (affichée 7, comme le legacy).
- [ ] L'encart du scrutin 3684 s'affiche au legacy même quand le député n'a pas
  voté (sans phrase) ; le port le masque. Non instruit.
- [ ] `nomin_principale` subsiste là où il donne encore le bon résultat
  (dernier rattachement ouvert : `DeputeListController`, `ElectionController`,
  `OutilsController`, `CalculClassementsCommand:508`, comptages en `DISTINCT`
  de `GroupeController` et `MoyennesAssemblee`). Juste aujourd'hui, mais fondé
  sur le même contresens : à basculer sur `GroupeAuScrutin::APPARTENANCE`.
- [ ] `CalculClassementsCommand` (repli sur `mandat`, députés sans
  `fonction_groupe`) ne garde que le premier mandat. Sans effet en 17e.
- [ ] `DeputeController::communeAvecDe` donne « de Hirson » ; `commune.nom_de`
  (rempli par `app:import:noms-communes`) donnerait « d'Hirson », comme le legacy.

### Groupes, statistiques, accueil
- [x] **Proximités** : groupes dissous, non-inscrits et groupes à 20 votes
  communs ou moins écartés, comme `get_stats_proximite` ; tableau complet
  dépliable « Voir tout le classement » ajouté.
- [x] **Renommage UDDPLR → UDR** par l'Assemblée (16/11) : `/udr` sert le groupe
  actif, l'ancien UDR vit sous `/udr-po847173`, `/uddplr` redirige en 301. Les
  265 adresses des plans de site répondent 200, les 62 autres groupes n'ont pas
  bougé.
- [x] **Pied « président / membres / apparentés »** sur la page des votes
  décryptés d'un groupe.
- [x] **Participation des groupes** : motions de censure exclues et moyenne
  arrondie à trois décimales avant le pourcentage, comme `class_groups` — sur
  la fiche, la moyenne de l'Assemblée et l'histogramme. Les 12 groupes
  concordent avec `/statistiques/groupes-participation` de datan.fr. Les
  non-votants n'y jouaient aucun rôle.
- [x] **Projets de loi du Gouvernement** (34 / 36) : le legacy exclut en dur
  `DLR5L16N49868` (simplification de la vie économique) ; repris dans
  `SoutienGouvernement::DOSSIERS_EXCLUS`.
- [x] **Âge moyen** : `EXISTS` au lieu d'une jointure sur `mandat`, qui pesait
  double les députés à deux mandats.
- [ ] Participation « par spécialisation » : 35 % en production contre 32 % au
  legacy (déjà relevé plus haut).
- [ ] Ordre des fenêtres modales du pied de page (`base.html.twig`), cosmétique.

### Résumés d'amendements par IA
- [x] **Chaîne par lots portée** (`app:ia:resumes-amendements:soumettre` /
  `:traiter`), six champs, prompt de la rédaction, Haiku 5.5, sorties
  structurées, consignes en cache. Tables `tag`, `amendement_tag`, `lot_ia`,
  `lot_ia_element`. 34 tests, aucun appel réel.
- [x] **Mots-clés** : normalisation sans IA, puis une requête groupée par
  traitement pour les seuls inconnus, sur un vocabulaire restreint ; chaque
  décision devient un alias.
- [x] **Reprise** des 6 509 résumés et 13 258 mots-clés du legacy
  (`app:import:resumes-amendements`).
- [ ] **Recharger le crédit Anthropic** : la chaîne du legacy échoue chaque
  heure depuis le 13/09 (« credit balance is too low »).
- [ ] Première mise en service avec une vraie clé : `--essai=5`, puis un lot de
  20, puis lire `lot_ia.jetons_cache_lus` avant de planifier (`CRON.md` §3).
- [ ] Après la reprise : `app:ia:tags:nettoyer` (simulation à relire, puis
  `--appliquer`). 4 124 libellés pour 6 509 amendements, 58 % employés une fois.
- [ ] **À trancher** : le résumé IA s'affiche sur la fiche vote dès qu'il
  existe, comme sur datan.fr (21 résumés relus sur 6 509) ; le port exigeait
  jusque-là `resume_relu`. Revenir en arrière tient en une condition du gabarit.
- [ ] **À trancher** : soumission toutes les 6 h par lots de 100 (le legacy :
  toutes les heures par 20) — le rapprochement des mots-clés pèse 10 % du lot
  au lieu de 30 à 40 %, mais un résumé peut attendre 7 h.
- [ ] Plus tard : rapprochement des mots-clés entre catégories ; relever
  l'exposé envoyé (300 caractères) avec la rédaction.

### Municipales 2026
- [x] **Portées** : `app:import:municipales` (fichiers du ministère),
  `app:import:noms-communes`, blocs de la page commune, habillage de la page
  département, encart du second tour. 54 119 listes, 36 362 lignes de
  participation ; le legacy en porte 54 105 et 36 362 — les 14 listes d'écart
  sont celles de quatre communes de Nouvelle-Calédonie sans numéro de panneau,
  qui s'y écrasent. 26 pages sur 31 identiques à datan.fr.
- [ ] Régénérer `var/legacy/candidatures.tsv` **sans `ORDER BY`** puis
  `app:import:elections` : sans cela `candidature.ordre_saisie` reste nul et
  les députés candidats sortent dans l'ordre des `mpId`.
- [ ] **À trancher** : textes de campagne mis au passé (« s'est tenu ») là où
  le legacy écrit encore « se tiendra ».
- [ ] Farino (98806) : publication du ministère incomplète, second tour affiché
  « en attente ».
- [ ] **Candidats municipales fiche DEM** (21 / 18, plus haut) : hypothèse non
  vérifiée — `GroupeController::candidatsMunicipales` ne compte que les députés
  en exercice, le legacy tous les candidats rattachés au groupe.
- [x] **Tables `elect_bv_*`** : remplies au legacy depuis le portage (9 M de
  lignes), mais lues pour le seul bloc « Législatives 2024 » d'une commune, que
  le port sert à l'identique depuis `resultat_legislative`. Pas une perte.

### Récupération de la base du legacy
Inventaire des 104 tables fait sur la vraie base (lecture seule). Outillage :
`bin/recuperation/exporter-legacy.sh`, `app:recuperation:legacy`,
`bin/recuperation/exporter-hors-sync.sh` (cf. CLAUDE.md et `DEPLOIEMENT.md`).
- [x] **Comptes** : les 72 comptes se reprennent, lecteurs compris
  (`ROLE_LECTEUR`), mot de passe inchangé. Le port n'en a que 4, d'essai.
- [x] **Auteurs des décryptages** rattachés par `utilisateur.id_origine`
  (255 sur 256 ; le dernier vient d'un compte supprimé).
- [x] **Amendements des 15e et 16e** (`app:import:amendements-clos`) : exposé
  des motifs et auteur de 5 705 amendements, absents de tout dépôt — sur
  `/votes/legislature-16/vote_119`, datan.fr affiche l'exposé, l'auteur et le
  résumé, le port aucun des trois.
- [x] **Résumés d'exposé de la 16e** (`app:import:exposes`) : 599 publiés, dont
  586 rattachables.
- [x] **Campagnes de dons** (`app:import:campagnes`), 2 lignes.
- [ ] **À rejouer sur le serveur** (cf. « Mise en production ») : newsletter
  (1 145 abonnés, 2 au port), professions de foi (2 197, plus 1,9 Go de PDF à
  copier), article `municipales-2026` et auteurs des 18 articles.
- [ ] Tester une restauration de l'export hors sync sur une base MariaDB
  jetable (vérifiée seulement sur MySQL 8).
- [ ] **À trancher** : 2 clés d'API en écriture (PoliticAnalysis), la trace de
  `table_history`, les valeurs tiktok/instagram (affichées nulle part), le sort
  des trois comptes d'essai du port, les brouillons de résumés de la 16e
  (importés « à relire »).
- [ ] `ImportAnnexesCommand`, rendue obsolète par `amendements-clos` : à supprimer.
- [ ] Non portés mais régénérables depuis l'open data, donc pas des pertes :
  `questions` (onglet de l'iframe), `reunions_*` (aucune page).

### Mise en production de ces corrections
Sur le serveur, dans `~/symfony.datan.fr/current`, `php` = `~/symfony.datan.fr/bin/php`,
toujours `APP_ENV=prod`, **jamais pendant une synchronisation** (cron à 6 h,
6 h 20 ensuite). Les étapes 3 à 12 écrivent dans la base de production.

- [ ] **0. Avant** : recharger le crédit Anthropic ; trancher les « À trancher »
  ci-dessus ; sauvegarde de la base du jour vérifiée (`shared/sauvegardes`).
- [ ] **1. Déployer** (push sur `symfony`). Quatre migrations partent avec le
  code, et doivent partir avec lui : `…100000` (comptes, journal de
  récupération), `…110000` (tags, lots IA), `…120000` (municipales), `…140000`
  (clé des mandats — l'ancien import, sans elle, insérerait des doublons).
  *Contrôle* : `doctrine:migrations:status` à jour, accueil en 200.
- [ ] **2. Cron** : `bash bin/cron.sh afficher`, puis `installer`.
  *Contrôle* : `crontab -l | grep "20 6"`.
- [ ] **3. Synchronisation complète** : `php bin/console app:sync:quotidien --tout --no-ansi`
  (12 à 25 min). Ramène les scrutins 9156-9200, les 223 amendements manquants
  et les auteurs ; adopte les 3 839 mandats par leur uid et insère les 122
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
  (~3 min), puis `app:import:noms-communes`.
  *Contrôle* : 54 119 listes, 36 362 lignes de participation.
- [ ] **9. Calculs** : `app:calcul:statistiques-deputes --legislature=15`, puis
  `--legislature=17`, puis `app:calcul:classements` (~3 min).
  *Contrôle* : loyauté de Chenu à 95 % sur 1 041 votes.
- [ ] **10.** Copier `~/public_html/assets/data/professions/` (1,9 Go) dans le
  `public/` partagé de l'application.
- [ ] **11.** `app:cache:rechauffer` (~6 min).
- [ ] **12. Contrôles en ligne** : `vote_9178` (17e) en 200 ; `vote_2751` (15e)
  avec sa ligne MODEM et 568 votants ; Chenu « Rassemblement National · POUR ·
  REBELLE » sur `vote_8280` ; bloc municipales sur
  `/elections/resultats/bouches-du-rhone-13/ville_marseille` ; exposé, auteur
  et résumé sur `/votes/legislature-16/vote_119` ; connexion d'un compte repris.
- [ ] **13. Résumés IA** (quand le crédit est là) : `ANTHROPIC_API_KEY` dans
  `shared/.env.local`, `soumettre --simulation`, `--essai=5`, un lot de 20,
  lecture de `lot_ia`, `app:ia:tags:nettoyer`, puis bascule des crons
  (`CRON.md` §3).
- [ ] **Jour de la bascule** : refaire 6 et 7 le plus tard possible (la
  newsletter continue de s'inscrire au legacy), puis
  `app:recuperation:legacy --clore` et suppression de `~/recuperation-legacy/`.

### Différences assumées nées de ces corrections (à ne pas « corriger »)
- Présidence de groupe exigée **en cours** : datan.fr écrit encore que Marine
  Le Pen « en est la présidente ».
- Commission affichée pour les 30 députés que le legacy laisse sans (il prend
  sa préséance minimale sur les mandats clos).
- 17e législature : « un groupe classé à droite » là où les deux sites
  écrivaient de chaque groupe qu'il « regroupe les députés non affiliés à un
  groupe parlementaire » (branche par défaut, faute de `positionPolitique`).
- `/groupes/legislature-17/udr` sert le groupe actif (le legacy y sert l'ancien
  groupe dissous) ; badge UDR conservé dans les coalitions, que le legacy a perdu.
- Municipales : « à La Rochelle » (legacy : « à Rochelle »), « 1 siège »
  (« 1 sièges »), les cinq listes de Belep (une seule au legacy), Merry-la-Vallée
  pourvue au premier tour ; Paris suit les chiffres corrigés par le ministère
  (865 370 votants au second tour, 865 366 au legacy).
- « dissous » au lieu de « dissout », « du bloc central », « métier ».
- Justification IA échappée (le legacy l'injecte brute).

## Déploiement sur symfony.datan.fr (compte o2switch de datan.fr)

Automatisation posée le 2026-10-23 (`deploiement.yml`, `bin/deployer.sh`,
`bin/cron.sh` ; DEPLOIEMENT.md §4). Reste, à la main :

- [ ] Ajouter `concurrency: { group: o2switch-datan }` à `deploy.yml` **sur
  `master`** : sans cela, un déploiement du legacy peut retirer l'IP de notre
  runner de la liste blanche SSH en plein rsync.
- [ ] cPanel : base + compte MariaDB, sous-domaine `symfony.datan.fr` sur
  `symfony.datan.fr/public` (lien vers `current/public` posé par le
  déploiement), certificat. Renommer le clone posé dans `~/symfony.datan.fr`. PHP 8.4 : par le `.htaccess`,
  surtout pas par le sélecteur du compte (il passerait datan.fr en 8.4).
- [x] Version MariaDB du serveur relevée : 11.4.13 (`qualite.yml` aligné).
  `ALTER DATABASE … CHARACTER SET utf8mb4` : le serveur est en latin1 par défaut.
- [x] `intl` non chargée par `/opt/alt/php84/usr/bin/php` : réglé en ligne de
  commande par le lanceur `~/symfony.datan.fr/bin/php` (`preparer_php`).
- [x] **`intl` et `dom` côté web** — réglé le 2026-10-23 en pointant
  `/opt/alt/php84/link/conf` (lien du compte) vers la configuration du sélecteur
  `/etc/cl.php.d/alt-php84` : `brancher_config_selecteur`, à chaque
  déploiement et par cron chaque minute (DEPLOIEMENT.md §4). Écarté : épingler
  le legacy en 7.4 par `.htaccess` — une version non sélectionnée perd 30
  extensions, `json` et `mbstring` comprises, datan.fr tomberait.
- [ ] Certificat Let's Encrypt de `symfony.datan.fr` (cPanel).
- [ ] `~/symfony.datan.fr/.env.local` et `.my.cnf` (pas de mot de passe de
  préproduction : site ouvert, non indexé par l'application).
- [ ] Restaurer le dump, premier déploiement, clones Tricoteuses,
  `bin/cron.sh afficher` puis `installer`.
- [ ] Passage du compte en PHP 8.4 : vérifier le legacy sur datan.fr le jour même.

## Checklist de bascule (rien à coder, à dérouler le jour J)

- [ ] Rejouer la récupération contre la **vraie** base de production, par
  `exporter-legacy.sh` puis `app:recuperation:legacy` (cf. « Corrections du
  2026-11-16 », mise en production) — jamais depuis le backup anonymisé. La
  refaire le jour J le plus tard possible, puis `--clore`.
- [ ] Rebrancher **Matomo** (matomo.datan.fr, siteId 1) — absent du staging à
  dessein (`MATOMO_URL` vide). GTM n'est plus à rebrancher : le site l'a retiré
  le 10/09/2026 avec Tarte au citron (cf. § Rattrapage legacy 2026-10-21).
- [ ] **Newsletter**, dans cet ordre :
  1. exporter les abonnés (requête dans le docblock d'`ImportNewsletterCommand`)
     puis `app:import:newsletter` — **le plus tard possible**, le legacy
     continuant d'inscrire jusqu'à la bascule ;
  2. poser `MAILJET_API_KEY`/`SECRET` et `MJML_APP_ID`/`SECRET_KEY` dans le
     `.env.local` de production (clés du `.htaccess` du legacy) ;
  3. `app:newsletter:votes test` → vérifier la lettre reçue à info@datan.fr ;
  4. retirer du crontab l'entrée `newsletter votes send` du legacy (et
     `newsletter update`), décommenter `app:newsletter:votes` dans
     `bin/crontab.prod` à la même heure, `bin/cron.sh installer` ;
  5. inscrire une adresse d'essai, vérifier bienvenue + listes chez Mailjet,
     puis `/newsletter/edit/<adresse>` et `/newsletter/delete/<adresse>`.
- [ ] Décider si l'**iframe** des députés doit porter Matomo : le legacy l'y
  charge (`_header_iframe.php`), notre `IframeController` l'en a retiré —
  sans lui, les affichages chez les sites tiers ne sont plus comptés.
- [ ] Vérifier que canonical/og:url basculent bien sur datan.fr (générés depuis
  l'hôte : automatique, mais vérifier).
- [ ] Vérifier les images `posts/inside/` (cf. P1) une fois le domaine basculé.
- [ ] Rejouer une passe de vérification du sitemap : chaque URL annoncée → 200
  (le critère de `SitemapController` se vérifie en interrogeant réellement).
- [ ] **datan.fr est derrière Cloudflare** (nuage orange), quand
  symfony.datan.fr ne l'est pas : la préproduction ne dit rien de ce cas.
  Avant de pointer datan.fr sur la nouvelle application, vérifier le mode SSL de
  la zone — en « Flexible », PHP reçoit du `http`, les canoniques sortent en
  `http://` et le cache réchauffé sous `https://` (`DEFAULT_URI`) ne sert à
  personne : exiger « Full (strict) » — et décider si Symfony doit lire l'IP
  des visiteurs dans `CF-Connecting-IP` (`trusted_proxies`).
- [ ] Retirer le bloc « PRÉPRODUCTION » de `public/.htaccess` (DEPLOIEMENT.md
  §6) : sans cela, les images et PDF de datan.fr porteraient `X-Robots-Tag:
  noindex`.

## Différences assumées — ne PAS « corriger » (consignées pour mémoire)

- Participation des groupes sur la fiche vote (DR 15→17 %, EPR 13→14 %) : correction
  documentée du bug legacy des non-votants (`App\Groupe\ParticipationGroupe`).
- Badges d'explication de vote « -1/1 » → « POUR/CONTRE » stylés (le legacy rendait
  la valeur brute non stylée).
- Coquilles corrigées (« Découvez », « présiedent », « vigeur », « consenement »,
  « Assemblée national », « ralantissant », mojibake « JÃ©rÃ´me »…), élisions
  (« d'Ajaccio »), « 15ème législature » figée, h1 vide des Français de l'étranger,
  « 0 député » affiché, année de copyright du login.
- Liens morts legacy remplacés : `/cookiespolicy` → `/mentions-legales`,
  `recherche.php` → géré en JS, rubriques blog « rapports » en dur → vraie rubrique,
  phrase « cliquez ici » à `href="#"` supprimée sur `/votes/decryptes/environnement`.
- Communes à parenthèses accessibles (le legacy les liait mais les refusait en 400).
- Ajouts : pages `/commissions`, API Platform lecture seule sous `/api`, pages
  `/legislature-N`, listes historiques de groupes, sitemap inactifs étendu L14-16.
- Recherche : votes triés par récence + filtre `state='published'` (documenté dans
  `SearchController`, faute d'index FULLTEXT équivalent au MATCH legacy).
- Tables `elect_bv_*` : toujours hors périmètre (cf. CLAUDE.md) — le legacy
  les a remplies mais ne les lit que pour un bloc que le port sert autrement.
  Les municipales 2026, elles, sont portées.
- Emails en clair et absence d'obfuscation Cloudflare : infrastructure, pas
  l'application.

## Reste à vérifier (couverture incomplète de la campagne du 2026-08-08)

- [ ] Trois sections n'ont pas eu leur comparaison HTML fine (agents interrompus) :
  **sous-pages groupes** (`/membres`, `/statistiques`, `/votes`, groupe dissous
  ecolo L14, groupe NI), **fiche d'un vote décrypté** (texte du décryptage, votes
  similaires), **fiche député en HTML** (JSON-LD, liens sociaux). Elles ont été
  couvertes par la comparaison visuelle et des sondages ciblés (`/groupes`,
  vote_1 L16, vote_1243 L16 : conformes hors points déjà listés), mais une passe
  HTML complète reste à faire.
- [ ] Rejouer la comparaison des pages touchées après chaque correction ci-dessus
  (une URL à la fois côté datan.fr, cf. rappel 429 en tête de fichier).

## Rattrapage legacy 2026-10-21 (`430e4625` → `58ac7f64`)

Relevé des 35 fichiers modifiés sur `datanfr/datan` depuis la base du portage
(4 juin → 16 octobre 2026). Les commits de fin juillet (votes clés, accueil,
endpoint newsletter, relecture des amendements) étaient déjà portés ; le
mojibake des noms de candidats est corrigé chez nous à l'import. Le reste :

- [x] **Cookies (10/09)** : Tarte au citron et GTM retirés de `base.html.twig`,
  compteur de pages en localStorage (`donation-modal-localstorage.js` repris
  tel quel), `main.css` et CSS critiques recompilés copiés, variable `GTM_ID`
  supprimée partout (Twig, `.env`, `.env.dist`, `DEPLOIEMENT.md`).
- [x] **Communes des collectivités sans population (06/09)** :
  `App\Territoire\SeuilCommunes` — exemption du seuil et plafond de 20 pour
  Polynésie, Nouvelle-Calédonie, Saint-Pierre-et-Miquelon, Saint-Barthélemy et
  Saint-Martin, Wallis-et-Futuna ; appliquée aux pages département et ville,
  aux pastilles et pieds des pages de résultats, et aux deux plans de site
  (+45 adresses chacun, toutes vérifiées en 200). Titres « Commune(s) de … »,
  h2 « Découvrez le député » au singulier.
  - Assumé : le plafond n'est **pas** appliqué à l'index alphabétique des
    pages de résultats, où le legacy le subit par effet de bord (Polynésie :
    20 communes sur 48 chez lui, 48 chez nous).
  - Assumé : la fiche de ville suit la règle corrigée de la page département
    (le legacy n'a corrigé que `departement/index.php` ; sa `commune.php`
    masque toujours Saint-Pierre-et-Miquelon).
  - Assumé : « la députée » pour la Lozère (le legacy écrit « le député »).
- [x] **Fiche commune (06/09)** : bouton « Résultats des élections à X » (en
  clair au-delà de 500 habitants, masqué en deçà), évolution de population à
  l'entier « 4% ».
- [ ] **Mentions légales à relire par la rédaction** : le texte annonce encore
  des « cookies de mesure d'audience (Google) » et un compteur « soumis à
  consentement », alors que GTM et Tarte au citron sont partis le 10/09/2026 et
  que le compteur vit en localStorage. Texte légal reproduit tel quel ; à
  signaler plutôt qu'à réécrire de notre chef.
- [ ] `app:import:votes-cles` n'a plus d'objet (la sélection des positions
  importantes ne retient plus aucun vote de la 16e depuis le 29/07/2026) :
  docblock mis à jour, commande à supprimer à l'occasion.
- Sans objet pour nous : scripts batch IA de `package.json` (absents du dépôt),
  workflow de déploiement cPanel, hook `urlValidator`, lien « Get files » du
  menu admin, aller-retour Matomo du 10/09 annulé le 16/10.
