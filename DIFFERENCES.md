# Différences assumées avec datan.fr

Écarts **voulus** entre ce dépôt et le site d'origine. À ne pas « corriger » :
chacun répond à la règle de `CLAUDE.md` — parité sur ce qui est un choix,
correction sur ce qui est un défaut — et le code porte le commentaire qui dit
pourquoi. Tout nouvel écart tranché s'ajoute ici ; ce qui reste à instruire est
dans `TODO.md`.

## Calculs

- **Participation des groupes sur la fiche vote** (DR 15 → 17 %, EPR 13 → 14 %) :
  correction du traitement des non-votants (`App\Groupe\ParticipationGroupe`).
  Ailleurs — fiche du groupe, moyenne de l'Assemblée, classement —, la règle du
  legacy est reprise telle quelle, double arrondi compris.
- **Loyauté non jugée** quand le groupe n'a pas de position majoritaire
  (égalité, ou personne n'a voté). Le legacy rend « rebelle », ou « loyal » sur
  ses données de la 15e importées autrement (Ménard, NI, vote_813).
- **Membres du Gouvernement sur une motion de censure** : « NON-VOTANT » sans
  loyauté, là où le legacy écrit « REBELLE » sans position (vote_1, 16e).
- **Commission** affichée pour les 30 députés que le legacy laisse sans : il
  prend sa préséance minimale sur des mandats clos.
- **Présidence de groupe exigée en cours** : datan.fr écrit encore que Marine
  Le Pen « en est la présidente ».
- **Départage des barres « vote rarement avec »** par sigle : le legacy rend
  l'ordre physique de son résultat SQL sur les ex æquo, non reproductible. La
  phrase, elle, suit son tri (libellé en ordre d'octets).
- **Ordre des logos de « La position des groupes »** : par effectif
  décroissant ; la requête du legacy n'a aucun `ORDER BY`.

## Jeux de données ouverts (`app:opendata:publier`)

Colonnes, ordre et précision repris du legacy, coquille `socreCohesion`
comprise : ce sont des noms que des réutilisations lisent. Les écarts :

- **Scores de la 17e** pris dans nos classements, ceux des pages
  /statistiques : la participation « spécialité » diffère donc pour la plupart
  des députés — le legacy la calcule en incrémental et ne revient jamais sur un
  scrutin rattaché tardivement à son dossier (Alain David : 3 votes comptés,
  9 chez nous ; cf. `TODO.md`, « Votes par spécialisation »). L'indice de
  Rose suit de même notre « représentativité sociale », encore à instruire, et
  reste vide pour les non-inscrits, que nos classements de composition
  écartent.
- **Majorité de la 17e** vide au lieu de `0.000` pour tous : sans groupe
  « Majoritaire », il n'y a rien à mesurer.
- **Législatures closes, députés partis, groupes dissous** : valeurs du legacy
  figées le 16/11/2026 (`ressources/opendata/`), faute de classement chez nous.
  Un député qui quitte l'hémicycle après cette date perd donc ses scores de 17e
  dans le jeu, plutôt que d'en recevoir d'approchés.
- **Corrections** : nom du département pris au référentiel (« Val-d'Oise »,
  « Côte-d'Or », « Corse-du-Sud » ; le legacy écrit « Val-d'oise »), code corse
  en capitales (`2A`, pas `2a`), espaces finales ôtées (`job`, `villeNaissance`,
  `nom`), ancienneté recalculée sur nos mandats (trois anciens députés passaient
  de 1 à 6 ans chez le legacy).

## Groupes

- **`/groupes/legislature-17/udr` sert le groupe en activité** ; l'ancien UDR
  dissous vit sous `/udr-po847173`, et `/uddplr` redirige en 301. Le legacy
  sert l'ancien groupe sous `/udr` et rend 404 sur `/uddplr`. Le badge UDR est
  conservé dans les coalitions, le legacy l'a perdu au renommage.
- **17e législature** : « un groupe classé à droite » là où les deux sites
  écrivaient de chaque groupe qu'il « regroupe les députés non affiliés à un
  groupe parlementaire » (branche par défaut, faute de `positionPolitique`).

## Résumés d'amendements

- **Le résumé IA ne s'affiche au public qu'une fois relu** (`resume_relu`),
  alors que datan.fr le publie dès qu'il existe. Il est visible sans relecture
  dans `/admin/amendements`, où il se relit.
- La justification est échappée ; le legacy l'insère brute.
- Soumission toutes les 6 h par lots de 100 (le legacy : toutes les heures par
  20) : le rapprochement des mots-clés pèse 10 % du lot au lieu de 30 à 40 %.

## Élections et communes

- **Municipales 2026** : textes de campagne mis au passé (« s'est tenu ») là où
  le legacy écrit encore « se tiendra » ; « à La Rochelle » (« à Rochelle »),
  « 1 siège » (« 1 sièges ») ; les cinq listes de Belep (une seule au legacy,
  qui écrase les listes sans numéro de panneau) ; Merry-la-Vallée pourvue au
  premier tour, comme le dit le ministère ; Paris suit les chiffres corrigés
  par le ministère (865 370 votants au second tour, 865 366 au legacy).
  Farino (98806) affiche un second tour « en attente » : la publication du
  ministère est incomplète.
- **Collectivités sans population** : le plafond de 20 communes n'est pas
  appliqué à l'index alphabétique des pages de résultats, où le legacy le subit
  par effet de bord (Polynésie : 20 communes sur 48 chez lui) ; la fiche de
  ville suit la règle corrigée de la page département (la sienne masque
  toujours Saint-Pierre-et-Miquelon) ; « la députée » pour la Lozère.
- **Communes à parenthèses** accessibles : le legacy les lie mais les refuse
  en 400.
- La correction manuelle du maire de Berre-l'Étang n'est pas portée : le
  référentiel a été mis à jour depuis.
- **Tables `elect_bv_*`** (grain bureau de vote) non portées : le legacy ne les
  lit que pour le bloc « Législatives 2024 » d'une commune, servi ici à
  l'identique depuis `resultat_legislative`.

## Textes, liens et gabarits

- **Coquilles et accords corrigés** : « Découvez », « présiedent », « vigeur »,
  « consenement », « Assemblée national », « ralantissant », « REBELLEE »,
  « dissout », « de groupes de bloc central », « metier », mojibake
  « JÃ©rÃ´me »… ; élisions (« d'Ajaccio ») ; « 15ème législature » figée ; h1
  vide des Français de l'étranger ; « 0 député » ; année du login.
- **Capitalisations** de l'orthographe officielle gardées (« Val-d'Oise »,
  « Côtes-d'Armor », « 2A »/« 2B », « Polynésie française »).
- **Badges d'explication de vote** « POUR / CONTRE » stylés ; le legacy rend la
  valeur brute « -1 / 1 ».
- **Liens morts remplacés** : `/cookiespolicy` → `/mentions-legales`,
  `recherche.php` → géré en JS, rubrique « rapports » du blog en dur → vraie
  rubrique, « cliquez ici » à `href="#"` supprimé.
- **`<link rel="shortcut icon" href="#">` non repris** : `#` étant la page
  elle-même, le navigateur la redemandait en entier pour y chercher une icône.
- **Hémicycle de l'accueil** : WebP posé directement et préchargé ; le legacy
  ne précharge rien et troque l'extension du JPEG en JavaScript. Un navigateur
  sans WebP (Safari < 14) garde le dégradé et l'image basse définition.
- **Phrase sur la Covid-19 de `/statistiques`** (« à cause de la crise de la
  Covid-19, les députés ayant rejoint le parlement récemment ont des scores de
  participation plus faibles ») non reprise : écrite pour la 15e législature,
  elle est fausse depuis.
- **Explications de vote** : texte échappé (avec ses sauts de ligne) sur la
  fiche du vote et dans la fenêtre d'une explication, là où le legacy rend la
  saisie du député telle quelle.
- **« Tous les votes » d'un groupe, non-inscrits** : « des députés non
  inscrits » là où le legacy écrit « du groupe Députés non inscrits ».
- **Canonical des pages mois** : une seule forme, zéro-paddée ; le legacy a un
  double self-canonical.
- **Recherche** : votes triés par récence et filtrés sur `state = 'published'`,
  faute d'index FULLTEXT équivalent au `MATCH` du legacy.

## Ajouts

- Pages `/commissions`, pages `/legislature-N`, listes historiques de groupes,
  plan de site des inactifs étendu aux 14e-16e, groupes NI.
- API en lecture seule sous `/api`.

## Laissé de côté sciemment

- **Non repris de la base d'origine** : les deux clés d'API en écriture (la
  boucle IA externe est devenue interne), la trace de `table_history`, les
  valeurs TikTok et Instagram (affichées nulle part), les jetons expirés
  (`users_mp_link`, `password_resets`), le suivi des lots IA
  (`anthropic_batches`, qui repart de zéro).
- **Non portés mais régénérables depuis l'open data** : `questions` (onglet de
  l'iframe), `reunions_*` (aucune page).
- **Six scrutins de la 15e** (1027-1029, 2814, 2816, 3778) incomplets à la
  source comme au legacy : ventilation partielle, rien à reprendre.
- **Infrastructure, pas l'application** : courriels en clair et absence
  d'obfuscation — datan.fr est derrière Cloudflare, la préproduction non.
