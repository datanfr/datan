<?php if ($composition): ?>
    <div class="row bloc-pie" id="pattern_background">
        <div class="container py-3">
            <div class="row pt-5">
            <div class="col-12">
                <h2 class="text-center">Composition de l'Assemblée nationale</h2>
            </div>
            </div>
            <div class="row pt-3">
            <div class="col-12">
                <p class="text-center mb-0">
                Découvrez les <?= $groupesN ?> groupes politiques de l'Assemblée nationale
                </p>
            </div>
            </div>
            <div class="row mt-5 mb-5">
            <div class="col-lg-5 col-md-6">
                <p>À l'Assemblée, les députés se regroupent par affinité politique (socialiste, droite, libéral, etc.). Les groupes ont un rôle clé dans l'organisation du travail parlementaire. Actuellement, il y a <?= $groupesN ?> groupes, le plus grand étant le <a href="<?= base_url() ?>groupes/legislature-<?= legislature_current() ?>/<?= mb_strtolower($groupes[0]['libelleAbrev']) ?>"><?= $groupes['0']['libelle'] ?> (<?= $groupes['0']['libelleAbrev'] ?>)</a>, avec <?= $groupes['0']['effectif'] ?> sièges.</p>
                <p>L'Assemblée peut être divisée en 4 grands blocs ! 👇</p>
                <ul class="list-unstyled ml-lg-3">
                <li>🔴 <b>La gauche</b> (NFP) : <?= $blocs['left'] ?> députés</li>
                <li>🟡 <b>Le bloc central</b> (Renaissance et alliés) : <?= $blocs['central'] ?>
                    députés</li>
                <li>🔵 <b>La droite</b> (LR) : <?= $blocs['right'] ?> députés</li>
                <li>🟤 <b>L'extrême droite</b> (RN et alliés) : <?= $blocs['extreme_right'] ?> députés</li>
                </ul>
                <div class="card coalition d-none d-lg-block mt-4">
                <div class="card-body">
                    <h2 class="card-title">Composez votre coalition</h2>
                    <p>Depuis les <a class="text-white" href="<?= base_url() ?>elections/legislatives-2024">élections de 2024</a>, aucun groupe n'a la majorité. Ils doivent s'allier pour faire passer des lois. Testez notre simulateur de coalition !</p>
                    <a href="<?= base_url() ?>outils/coalition-simulateur" class="btn btn-light">Formez votre coalition</a>
                </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-6 d-flex flex-column justify-content-center mt-3 mt-md-0">
                <div class="hemicycle">
                <canvas id="chartHemicycle"></canvas>
                <div class="n-hemicycle text-center">
                    <span>577 députés</span>
                </div>
                </div>
            </div>
            <div class="col-12 d-lg-none mt-3">
                <div class="card coalition">
                <div class="card-body">
                    <h2 class="card-title">Composez votre coalition</h2>
                    <p>Depuis les <a class="text-white" href="<?= base_url() ?>elections/legislatives-2024">élections de 2024</a>, aucun groupe n'a la majorité. Ils doivent s'allier pour faire passer des lois. Testez notre simulateur de coalition !</p>
                    <a href="<?= base_url() ?>outils/coalition-simulateur" class="btn btn-light">Former votre coalition</a>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
<?php endif; ?>