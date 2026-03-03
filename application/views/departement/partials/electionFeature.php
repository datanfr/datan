<!-- Election Feature -->
<div class="card card-election-feature information-success border-0" style="overflow: hidden">
    <div class="card-body">
        <h2 class="text-white"><?= $title ?></h2>
        <?php if (empty($election)): ?>
            <p class="mb-0">Il n'y a aucun député candidat aux élections municipales de 2026 dans la ville <?= $city_info['nom_de'] ?>.</p>
        <?php elseif (count($election) === 1): ?>
            <?php $candidate = $election[0]; ?>
            <p class="mb-0"><?= ucfirst(gender($candidate['civ'])['le']) ?> <?= gender($candidate['civ'])['depute'] ?> <a class="text-white" href="<?= base_url() ?>deputes/<?= $candidate['dptSlug'] ?>/depute_<?= $candidate['nameUrl'] ?>"><?= $candidate['nameFirst'] ?> <?= $candidate['nameLast'] ?></a> est candidat<?= gender($candidate['civ'])['e'] ?> aux élections municipales de 2026 dans la ville <?= $city_info['nom_de'] ?>.</p>
        <?php else: ?>
            <p class="mb-0">Les députés 
            <?php
            $names = array_map(function($c) {
                return '<a class="text-white" href="' . base_url() . 'deputes/' . $c['dptSlug'] . '/depute_' . $c['nameUrl'] . '">' . $c['nameFirst'] . ' ' . $c['nameLast'] . '</a>';
            }, $election);
            echo implode(' et ', array_filter([
                implode(', ', array_slice($names, 0, -1)),
                end($names)
            ]));
            ?> sont candidats aux élections municipales de 2026 dans la ville <?= $city_info['nom_de'] ?>.</p>
        <?php endif; ?>
        <?php if($link): ?>
            <a href="<?= base_url() ?>elections/municipales-2026" class="mt-3 btn btn-light">En savoir plus sur ces élections</a>
        <?php endif; ?>
    </div>
</div>