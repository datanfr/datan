<?php

namespace App\Ia\Tag;

use Anthropic\Client;
use Psr\Log\LoggerInterface;

/**
 * Rattache aux amendements les mots-clés proposés par le modèle, en passant
 * par le vocabulaire : un libellé connu (directement ou par alias) se rattache
 * sans IA ; les inconnus de tout le traitement partent ensemble dans une seule
 * requête de {@see RapprochementTags}, dont chaque décision devient un alias.
 *
 * Sans client (import de récupération, ou modèle injoignable), les inconnus
 * deviennent des canoniques : le nettoyage (`app:ia:tags:nettoyer`) les
 * rapprochera plus tard. Une panne du rapprochement ne fait donc jamais perdre
 * un mot-clé, elle diffère seulement son rangement.
 */
final class AttributionTags
{
    public function __construct(
        private readonly VocabulaireTags $vocabulaire,
        private readonly RapprochementTags $rapprochement,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<int, array{categorie: ?string, tags: list<string>}> $propositions par id d'amendement
     *
     * @return array{connus: int, nouveaux: int, alias_existant: int, alias_candidat: int, liens: int, rapprochement: ?string}
     */
    public function attribuer(array $propositions, ?Client $client = null, string $modele = ''): array
    {
        $bilan = ['connus' => 0, 'nouveaux' => 0, 'alias_existant' => 0, 'alias_candidat' => 0, 'liens' => 0, 'rapprochement' => null];

        // 1. Ce que le vocabulaire sait déjà.
        $candidats = [];   // slug => [libellé, catégorie]
        $frequence = [];   // slug => nombre d'amendements qui le proposent ici
        $parAmendement = []; // id => list<slug>
        foreach ($propositions as $amendementId => $proposition) {
            foreach ($proposition['tags'] as $libelle) {
                $slug = NormaliseurTag::slug($libelle);
                if ($slug === '' || \strlen($slug) > NormaliseurTag::LONGUEUR_MAX) {
                    continue;
                }
                $parAmendement[$amendementId][] = $slug;
                if ($this->vocabulaire->canonique($slug) !== null) {
                    ++$bilan['connus'];
                    continue;
                }
                $candidats[$slug] ??= [$libelle, $proposition['categorie']];
                $frequence[$slug] = ($frequence[$slug] ?? 0) + 1;
            }
        }

        // 2. Les inconnus : une requête, s'il y en a et si l'on peut.
        $decisions = array_fill_keys(array_keys($candidats), null);
        if ($candidats !== [] && $client !== null) {
            [$usage, $usageParCategorie] = $this->vocabulaire->usages();
            $vocabulaire = $this->vocabulaire->canoniques();
            $sections = RapprochementTags::sections($candidats, $vocabulaire, $usage, $usageParCategorie);
            $montres = 0;
            foreach ($sections as $section) {
                $montres += \count($section['existants']);
            }

            try {
                $couples = $this->rapprochement->rapprocher($client, $modele, $sections);
                $decisions = RapprochementTags::resoudre(
                    $couples,
                    array_map(static fn (array $c) => $c[0], $candidats),
                    // Une cible se valide contre tout le vocabulaire, pas
                    // seulement contre ce qui a été montré : un modèle qui
                    // rattache « interruption volontaire de grossesse » à
                    // « IVG » sans qu'on le lui ait montré nomme un mot-clé
                    // qui existe bel et bien.
                    $vocabulaire,
                    $usage + $frequence,
                );
                $bilan['rapprochement'] = sprintf('%d candidat%s, %d existant%s montrés, %d couple%s proposés',
                    \count($candidats), \count($candidats) > 1 ? 's' : '',
                    $montres, $montres > 1 ? 's' : '',
                    \count($couples), \count($couples) > 1 ? 's' : '');
            } catch (\Throwable $e) {
                // Le résumé est écrit, ses mots-clés ne doivent pas se perdre :
                // ils entrent comme canoniques, le nettoyage rattrapera.
                $this->logger->warning('Rapprochement des tags en échec, inconnus créés tels quels', ['exception' => $e]);
                $bilan['rapprochement'] = 'échec : ' . $e->getMessage();
            }
        }

        // 3. Écriture : les nouveaux canoniques d'abord, puis les alias qui
        // peuvent pointer vers eux.
        foreach ($decisions as $slug => $cible) {
            if ($cible === null) {
                $this->vocabulaire->creer($candidats[$slug][0], $slug);
                ++$bilan['nouveaux'];
            }
        }
        foreach ($decisions as $slug => $cible) {
            if ($cible !== null) {
                $existant = !isset($candidats[$cible]);
                $this->vocabulaire->creer($candidats[$slug][0], $slug, $this->vocabulaire->canonique($cible));
                ++$bilan[$existant ? 'alias_existant' : 'alias_candidat'];
            }
        }

        // 4. Les liens, toujours vers le canonique.
        foreach ($parAmendement as $amendementId => $slugs) {
            $ids = array_values(array_filter(array_map($this->vocabulaire->canonique(...), $slugs)));
            $bilan['liens'] += $this->vocabulaire->rattacher($amendementId, $ids);
        }

        return $bilan;
    }
}
