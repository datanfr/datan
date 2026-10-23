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
   qu'il vient d'écrire : elles se chaînent en `&&` dans la même entrée, sinon
   une journée chargée les fait démarrer avant la fin du sync.

## 1. Quotidien — 6 h

Les Tricoteuses remoissonnent l'open data de l'Assemblée dans la nuit : à 6 h la
source est fraîche, et la base est à jour au réveil.

| Commande | Rôle | Durée |
| --- | --- | --- |
| `app:sync:quotidien` | Moisson + 11 étapes d'import, dans l'ordre des dépendances | ~50 s à vide |
| `app:import:commissions` | Mandats COMPER (25 000 lignes) — hors chaîne, mais lit le **même delta** | quelques s |
| `app:calcul:statistiques-deputes` | Participation, loyauté, proximité par groupe de la fiche député | ~15 s |
| `app:cache:rechauffer` | Vide le cache HTTP puis y remet les ~2 500 pages les plus lues (accueil, listes, députés en exercice, groupes, partis, départements, élections, blog, scrutins décryptés ; pas les communes). Les pages restent en cache un jour : c'est cette purge qui rend les nouveaux scrutins visibles, et le réchauffage qui évite au premier visiteur d'attendre le rendu (0,3 à 1,7 s au lieu de ~30 ms) | ~15 min |

`app:sync:quotidien` déroule lui-même, dans cet ordre : moisson des dépôts,
acteurs, organes hors table dédiée, profils sociaux, photos, **détourage des
photos**, dossiers, amendements, scrutins, comptes rendus, rattachement
scrutin → amendement, classements. Rien à planifier séparément là-dedans.

Le détourage suit la publication des photos parce qu'il en dépend : il ne
retraite que ce que l'étape précédente vient d'écrire, et coûte le temps d'un
`filemtime()` par portrait déjà fait. Un premier passage sur les 647 portraits
prend 12 s.

Les deux commandes qui la suivent **ne sont pas dans la chaîne à dessein** —
ce sont des précalculs qu'on lance sciemment — mais elles se périment dès qu'un
scrutin entre : un nouveau vote décale participation, loyauté et proximité,
et un changement de commission ne se voit pas sans le second import. Leur place
est donc juste après le sync, dans la même entrée.

### Windows (poste de travail)

Déjà outillé : `bin\sync-quotidien.bat` (pose `APP_ENV=prod`, journalise dans
`var\log\sync-quotidien.log`, purge le cache si et seulement si le sync a
réussi), enregistré par

```powershell
pwsh -File bin\planifier-sync.ps1                # tous les jours à 06:00
pwsh -File bin\planifier-sync.ps1 -Heure 05:30
pwsh -File bin\planifier-sync.ps1 -Supprimer
Start-ScheduledTask -TaskName datan-sync-quotidien   # exécution immédiate, pour test
```

La tâche rattrape les exécutions manquées (machine éteinte) et se coupe au bout
de 2 h. **`bin\sync-quotidien.bat` ne lance encore que `app:sync:quotidien`** :
les deux lignes du tableau ci-dessus sont à y ajouter le jour où on les
planifie.

### Serveur (crontab)

La table réellement installée est **`bin/crontab.prod`** (`crontab
bin/crontab.prod`) : elle porte aussi la sauvegarde, le consommateur Messenger
et la rotation des journaux. L'extrait ci-dessous est mis en forme pour la
lecture — **cron arrête la commande à la fin de la ligne**, la continuation par
antislash n'est pas garantie d'une implémentation à l'autre.

```cron
# Mise à jour quotidienne — 6 h. Une seule entrée : l'ordre est garanti par &&,
# et la purge du cache n'a lieu que si tout ce qui précède a réussi.
0 6 * * * cd /var/www/datan && sh -c 'APP_ENV=prod php bin/console app:sync:quotidien --no-ansi \
  && APP_ENV=prod php bin/console app:import:commissions --no-ansi \
  && APP_ENV=prod php bin/console app:calcul:statistiques-deputes --no-ansi \
  && APP_ENV=prod php bin/console app:cache:rechauffer --no-ansi' >> var/log/sync-quotidien.log 2>&1
```

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
| `app:ia:resumes-amendements` | quotidien, après le sync | **Pas avant le choix d'`IA_MODELE` de production.** La garde « jamais écraser un résumé existant » fait qu'un lot passé au modèle d'essai local bloquerait pour toujours un lot plus propre. Options : `--jours=30 --limite=50` par défaut. Chaque exécution appelle un modèle (local ou facturé). |
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
  `app:import:faq`, `app:import:quiz`, `app:import:parrainages`,
  `app:import:reseaux-sociaux`, `app:import:decryptages` — contenu éditorial et
  comptes, données de production non regénérables.
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
- **Newsletter mensuelle** (`newsletter/votes` en CLI dans le legacy, composition
  MJML + Mailjet). Rien de porté à ce jour.
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

- Journal : `var/log/sync-quotidien.log` — une ligne `===== date =====` par
  exécution, `ECHEC (code N)` en cas de sortie non nulle. Le sync s'arrête à la
  première étape en échec et n'exécute pas la suite (donc pas de purge de cache,
  donc les pages servies restent celles de la veille : cohérentes, pas
  incomplètes).
- Un import qui écarte des lignes en rend le compte à l'unité. Un écart qui
  grandit d'un jour à l'autre est le signe d'une règle devenue fausse, pas d'une
  source incomplète.
