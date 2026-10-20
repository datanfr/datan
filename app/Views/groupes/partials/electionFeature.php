<!-- Election Feature -->
<?php if (isset($electionFeature)): ?>
    <div class="card card-election-feature information-success mb-5 border-0" style="overflow: hidden">
        <div class="card-body">
            <h2 class="text-white">🗳️ Municipales 2026</h2>
            <?php if ($electionFeature == 0): ?>
                <p class="mb-0">Aucun député du groupe <?= $groupe['libelleAbrev'] ?> n'est candidat aux élections municipales de 2026.</p>
            <?php elseif ($electionFeature > 0): ?>
                <p class="mb-0">Il y a <?= $electionFeature ?> député<?= $electionFeature > 1 ? "s" : "" ?> membre<?= $electionFeature > 1 ? "s" : "" ?> du groupe <?= $groupe['libelleAbrev'] ?> candidat<?= $electionFeature > 1 ? "s" : "" ?> aux élections municipales de 2026.</p>
            <?php endif; ?>
            <a href="<?= base_url() ?>elections/municipales-2026" class="mt-3 btn btn-light">En savoir plus sur ces élections</a>
        </div>
    </div>
<?php endif; ?>