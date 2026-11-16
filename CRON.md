# Planification

Ce que Datan doit faire tourner tout seul, et à quel rythme. Les commandes
absentes de ce fichier ne se planifient pas — la liste du §4 dit pourquoi.

**Trois règles valent pour toutes les entrées ci-dessous** (détail dans
`CLAUDE.md`) :

1. **`APP_ENV=prod` impérativement.** En dev, le logger Doctrine garde chaque
   requête en mémoire et fait échouer tout import volumineux.
2. **Jamais de `cache:clear` pendant une synchronisation.** Vider
   `var/cache/prod` sous une commande en cours la fait échouer sans message
   exploitable. Après coup, on ne vide que le cache HTTP, par
   `app:cache:rechauffer` — pas plus.
3. **Enchaîner, pas juxtaposer.** Les étapes qui suivent le sync dépendent de ce
   qu'il vient d'écrire : elles se suivent dans la même entrée, sinon une
   journée chargée les fait démarrer avant la fin du sync. Par `;` et non par
   `&&` : une synchronisation qui a écrit doit purger, même en échec.

## 1. Quotidien — 6 h 20

Les Tricoteuses remoissonnent l'open data de l'Assemblée dans la nuit : à 6 h 20
la source est fraîche, et la base est à jour au réveil. Pas à 6 h pile : c'est
la fenêtre où CloudLinux regénère la configuration PHP du compte, et où cinq
synchronisations sur vingt ont reçu un SIGHUP entre le 27/10 et le 15/11.

| Commande | Rôle | Durée |
| --- | --- | --- |
| `app:sync:quotidien` | Moisson + 13 étapes d'import, dans l'ordre des dépendances ; acquitte le delta si toutes réussissent, purge le cache HTTP dès qu'une étape a écrit | ~5 min en production |
| `app:calcul:statistiques-deputes` | Participation, loyauté, proximité par groupe de la fiche député | ~15 s |
| `app:cache:rechauffer` | Vide le cache HTTP puis y remet les ~2 500 pages les plus lues (accueil, listes, députés en exercice, groupes, partis, départements, élections, blog, scrutins décryptés ; pas les communes). Les pages restent en cache un jour : c'est cette purge qui rend les nouveaux scrutins visibles, et le réchauffage qui évite au premier visiteur d'attendre le rendu (0,3 à 1,7 s au lieu de ~30 ms) | ~6 min |

`app:sync:quotidien` déroule lui-même, dans cet ordre : moisson des dépôts,
acteurs, organes hors table dédiée, commissions, profils sociaux, photos,
**détourage des photos**, dossiers, amendements, auteurs des amendements,
scrutins (avec contrôle du dépôt entier contre la base), comptes rendus,
rattachement scrutin → amendement, classements. Rien à planifier séparément
là-dedans.

**Le delta n'est consommé qu'une fois importé.** Chaque dépôt porte
`.datan-acquitte`, le commit que la dernière chaîne entièrement réussie a
importé ; la moisson écrit dans `.datan-modifies` tout ce qui a changé depuis.
Une chaîne interrompue n'acquitte rien, et la suivante reprend son delta avec
le sien (« N fichiers à importer, en attente depuis <commit> » dans le tableau
de la moisson). C'est pourquoi toute commande qui lit le delta doit être une
étape de la chaîne : `app:import:commissions`, longtemps lancée après, y est
entrée.

Le détourage suit la publication des photos parce qu'il en dépend : il ne
retraite que ce que l'étape précédente vient d'écrire, et coûte le temps d'un
`filemtime()` par portrait déjà fait. Un premier passage sur les 647 portraits
prend 12 s.

`app:calcul:statistiques-deputes` **n'est pas dans la chaîne à dessein** —
c'est un précalcul qu'on lance sciemment — mais il se périme dès qu'un scrutin
entre : un nouveau vote décale participation, loyauté et proximité. Sa place est
donc juste après le sync, dans la même entrée.

### Windows (poste de travail)

Déjà outillé : `bin\sync-quotidien.bat` (pose `APP_ENV=prod`, journalise dans
`var\log\sync-quotidien.log`, purge le cache que le sync ait réussi ou non),
enregistré par

```powershell
pwsh -File bin\planifier-sync.ps1                # tous les jours à 06:00
pwsh -File bin\planifier-sync.ps1 -Heure 05:30
pwsh -File bin\planifier-sync.ps1 -Supprimer
Start-ScheduledTask -TaskName datan-sync-quotidien   # exécution immédiate, pour test
```

La tâche rattrape les exécutions manquées (machine éteinte) et se coupe au bout
de 2 h. **`bin\sync-quotidien.bat` ne lance encore que `app:sync:quotidien`** :
les statistiques et le réchauffage sont à y ajouter le jour où on les planifie.

### Serveur (crontab)

La table réellement installée est le gabarit **`bin/crontab.prod`**, posé par
`bin/cron.sh installer` — **jamais par `crontab bin/crontab.prod`**, qui
remplacerait la table du compte et ses tâches legacy (`DEPLOIEMENT.md` §5). Elle
porte aussi la sauvegarde, le consommateur Messenger et la rotation des
journaux. L'extrait ci-dessous est mis en forme pour la
lecture — **cron arrête la commande à la fin de la ligne**, la continuation par
antislash n'est pas garantie d'une implémentation à l'autre.

```cron
# Mise à jour quotidienne — 6 h 20. Une seule entrée, sourde au SIGHUP ; les trois
# commandes s'enchaînent dans l'ordre et le réchauffage (donc la purge) a lieu
# même si le sync a échoué. Un verrou pris se journalise « SAUTE », pas « ECHEC ».
20 6 * * * cd /var/www/datan && trap '' HUP && { echo "===== $(date) ====="; \
  flock -n -E 75 var/sync.lock sh -c 'APP_ENV=prod php bin/console app:sync:quotidien --no-ansi; s=$?; \
  APP_ENV=prod php bin/console app:calcul:statistiques-deputes --no-ansi; c=$?; \
  APP_ENV=prod php bin/console app:cache:rechauffer --no-ansi; r=$?; \
  echo "BILAN sync=$s statistiques=$c rechauffage=$r"; [ $s -eq 0 ] && [ $c -eq 0 ] && [ $r -eq 0 ]'; \
  e=$?; if [ $e -eq 75 ]; then echo "SAUTE : verrou pris"; elif [ $e -ne 0 ]; then echo "ECHEC (code $e)"; fi; \
  echo "===== fin $(date) ====="; } >> var/log/sync-quotidien.log 2>&1
```

### Newsletter — 5 h 30

`app:newsletter:desinscriptions` reporte en base les désinscriptions faites par
le lien d'une campagne Mailjet (un appel d'API par abonné, ~1 100). Sans clés
Mailjet, elle le dit et sort en 0 : l'entrée peut vivre en préproduction.

## 2. Hebdomadaire

| Commande | Rythme | Pourquoi pas plus souvent |
| --- | --- | --- |
| `app:scraper:scrutins` | 1×/semaine | Seule commande qui sorte sur un site public. Elle rattrape les 2 % de scrutins dont l'open data ne donne ni le dossier ni l'amendement. Plafond de 40 pages par lancement, 1,5 s entre deux requêtes (~1 min), et mémoire des visites infructueuses : une page vue sans rien n'est pas redemandée avant 30 jours. |

```cron
15 4 * * 1 cd /var/www/datan && APP_ENV=prod php bin/console app:scraper:scrutins --no-ansi >> var/log/scraper-scrutins.log 2>&1
```

Après le premier rattrapage (une centaine de pages), il ne reste que les
quelques scrutins de la veille. Volontairement **hors** de la chaîne quotidienne :
c'est le seul appel à `assemblee-nationale.fr`, il mérite d'être lancé sciemment.

## 3. À la demande — planifiables une fois seulement

| Commande | Rythme conseillé | Condition |
| --- | --- | --- |
| `app:ia:resumes-amendements:soumettre --taille=100` | toutes les 6 h | **Le jour de la bascule, pas avant** (ci-dessous). Soumet à l'API de lots d'Anthropic les amendements mis aux voix sans résumé ; `--simulation` montre la requête sans clé ni envoi. Demande `ANTHROPIC_API_KEY` ; modèle `IA_MODELE_RESUMES` (`claude-haiku-5-5`). |
| `app:ia:resumes-amendements:traiter` | toutes les heures, sous `flock` | Même condition. Intègre les lots terminés (24 h au plus), rapproche les mots-clés inconnus en une requête, purge le cache HTTP si un résumé est écrit. Sans lot terminé, aucun appel facturé. |
| `app:ia:tags:nettoyer` | jamais planifié | Une fois après `app:import:resumes-amendements`, puis quand le vocabulaire a dérivé. Simulation par défaut, `--appliquer` pour écrire, `--sans-appel` pour voir le découpage sans clé. ~2,5 centimes pour tout le vocabulaire repris. |

**Bascule des résumés d'amendements.** Le legacy fait tourner sur le même
compte deux crons horaires, `generate_amendements_ia_submit_batch.php` (:00) et
`generate_amendements_ia_process_batch.php` (:15). Les deux chaînes ne doivent
jamais soumettre en même temps — chacune paierait les amendements que l'autre
n'a pas encore écrits dans **sa** base :

1. commenter les **deux** lignes du legacy. Relevé le 15/11/2026 : sa chaîne est
   à l'arrêt depuis le 13/09 — chaque soumission échoue sur « credit balance is
   too low », le dernier lot date du 13/09 et le traitement rend « 0 amendement
   mis à jour » toutes les heures. Si elle a été relancée entre-temps (crédit
   rechargé), couper d'abord la soumission et laisser le traitement vider les
   lots en vol avant de couper la seconde ligne ;
2. ne pas attendre que `anthropic_batch_items` n'ait plus de `pending` : 161
   éléments y sont bloqués dans un lot qui ne se traitera plus. Sans résumé en
   base, ces amendements seront simplement soumis par le port ;
3. exporter `amendements_ia` et `amendements_tags` (requêtes dans le docblock
   d'`ImportResumesAmendementsCommand`), lancer `app:import:resumes-amendements`
   puis `app:ia:tags:nettoyer` (simulation, puis `--appliquer`) ;
4. décommenter les deux lignes de `bin/crontab.prod`, `bin/cron.sh installer`.

L'import ne réécrit rien d'existant : si l'étape 3 doit être rejouée, elle peut
l'être.
| `app:moissonner:bluesky --ecrire` | mensuel, ou après une vague d'arrivées | Interroge l'API publique de Bluesky. Ne touche que les députés **sans** poignée et n'écrit que les correspondances franches ; les autres candidats sont listés pour relecture. Sans `--ecrire`, elle ne fait que proposer. |
| `app:sync:quotidien --tout` | jamais planifié | Reconstruction complète (tous les dépôts, pas le delta). À la main, après un changement de schéma ou un import douteux. |

## 4. Jamais planifié

**Les imports de récupération réalignent sur la base de production.** Ils sont
faits pour le chargement initial ; une fois l'application devenue la source (un
rédacteur qui publie, un député qui écrit, une poignée trouvée par
`app:moissonner:bluesky`), les rejouer **écrase les écritures locales**. Aucun
n'a sa place dans une tâche planifiée.

- `app:import:communes`, `app:import:elections`, `app:import:resultats-electoraux`,
  `app:import:circonscriptions` — référentiels figés : un scrutin passé ne change
  plus, une commune non plus.
- `app:import:utilisateurs`, `app:import:explications`, `app:import:articles`,
  `app:import:resumes-amendements`,
  `app:import:faq`, `app:import:quiz`, `app:import:parrainages`,
  `app:import:reseaux-sociaux`, `app:import:decryptages`, `app:import:newsletter`
  — contenu éditorial, comptes et abonnés, données de production non
  regénérables.
- `app:import:deputes`, `app:import:mandats`, `app:import:fonctions-groupe`,
  `app:import:votes`, `app:import:vote-groupes`, `app:import:annexes` — amorçage
  historique (base de référence *canutes* et exports TSV du legacy). La chaîne
  Tricoteuses les a remplacés pour la 17e législature ; ils restent rejouables
  pour l'historique 14e-16e, que l'open data moissonné ne couvre pas.
- `app:utilisateur:creer` — ponctuel, à la main.

## 5. Pas encore branché — à planifier au déploiement

- **Publication data.gouv** (`opendata()` de `daily.php` dans le legacy) : les
  jeux CSV que notre pied de page pointe. Rythme du legacy : quotidien, hors
  `app:sync:quotidien`. Rien de porté à ce jour.
- **Lettre mensuelle des votes** : `app:newsletter:votes envoyer`, le 1er du
  mois (entrée commentée dans `bin/crontab.prod`). À décommenter **le jour de la
  bascule**, en retirant l'entrée `newsletter votes send` du legacy : les deux
  ensemble enverraient la lettre deux fois. Avant cela, `apercu` et `test`
  (à info@datan.fr) se lancent à la main. Demande `MAILJET_API_*` et
  `MJML_*` dans `.env.local`.
- **Consommateur Messenger.** `SendEmailMessage` est routé vers le transport
  `async` (`doctrine://default`) : sans worker, **aucun courriel ne part** —
  mot de passe oublié, demande de compte député, inscription. Ce n'est pas un
  cron mais un processus permanent, à superviser (systemd, Supervisor) :

  ```
  APP_ENV=prod php bin/console messenger:consume async --time-limit=3600
  ```

  À défaut de superviseur, une entrée cron toutes les heures avec ce
  `--time-limit` fait l'affaire, au prix d'une latence d'envoi.

## Surveiller

- Journal : `var/log/sync-quotidien.log` — `===== date =====` et
  `===== fin date =====` encadrent chaque exécution, chaque étape porte son heure
  de début (heure de Paris), `BILAN sync=… statistiques=… rechauffage=…` donne
  les trois codes, `ECHEC (code N)` une sortie non nulle, `SAUTE` un verrou
  pris. Le sync s'arrête à la première étape en échec, n'acquitte rien — la
  prochaine exécution reprend le delta — et purge quand même le cache HTTP si
  une étape a écrit.
- À chercher après un incident : `Hangup` (signal reçu), `MySQL server has gone
  away` (connexion perdue ; l'étape est retentée une fois), `en attente depuis`
  dans le tableau de la moisson (une chaîne précédente a échoué, son delta est
  repris), `Contrôle en échec` (un scrutin du dépôt manque à la base après
  rattrapage : la commande sort en 1), `Source incomplète` (l'open data ventile
  moins de voix qu'il n'en annonce — à reprendre du legacy).
- Un import qui écarte des lignes en rend le compte à l'unité. Un écart qui
  grandit d'un jour à l'autre est le signe d'une règle devenue fausse, pas d'une
  source incomplète.
