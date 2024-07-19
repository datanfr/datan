<div class='container pg-outils pg-outils-labo my-5'>
    <div class='row'>
        <div class='col-lg-8 offset-lg-2'>
            <h1 class='text-center'>Formez votre coalition à l'Assemblée nationale</h1>
            <p class='mt-5 h'><span class='font-weight-bold text-primary'>289</span> : c'est le nombre de députés nécessaire pour obtenir une <span class='font-weight-bold'>majorité absolue</span> à l'Assemblée nationale.</p>
            <p>Après les <a href="<?= base_url() ?>elections/legislatives-2024">élections législatives de 2024</a>, aucun groupe politique n'a atteint ce nombre. Les groupes devront donc <span class='font-weight-bold'>former des coalitions</span> pour arriver à faire voter des projets et propositions de loi.</p>
            <p>Essayez notre <span class='font-weight-bold'>simulateur de coalition</span>. Sélectionnez différents groupes de l'Assemblée nationale et découvrez si vous pouvez atteindre les 289 députés nécessaires pour une majorité absolue.</p>
        </div>
        <div class='col-12 mt-5'>
            <div class='containerHemicycle d-flex justify-content-center'>
                <?= file_get_contents(asset_url() . '/imgs/hemycicle_position/hemicycle_empty.svg') ?>
            </div>
            <p class='text-center h5 mt-5'>C'est une coalition de <span id='textSeats'>0 siège</span> <small>(<span id='textPct'>0</span> %)</small></p>
            <p class='text-center h5 mb-0'><span id='textResult' class='text-danger'>Vous n'avez pas la majorité</span> <small>(minimum 289 sièges)</small></p>
        </div>
    </div>
    <div class='row mt-5'>
        <div class='col-lg-8 offset-lg-2'>
            <div class='row'>
                <?php foreach($groups as $group): ?>
                    <div class='col-lg-3 col-md-3 col-6'>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switch_groups mx-2" id="<?= $group['libelleAbrev'] ?>">
                            <label class="custom-control-label" for="<?= $group['libelleAbrev'] ?>">
                                <span class="font-weight-bold" style="color: <?= $group['color'] ?>"><?= $group['libelleAbrev'] ?> (<?= $group['seats'] ?>)</span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class='row mt-3'>
                <div class='col-12 d-flex justify-content-center'>
                    <button type="button" id="custom-reset" class="btn btn-outline-dark">Réinitialiser</button>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    var groups = <?php echo json_encode($groups); ?>;
    Object.keys(groups).forEach(function(key) {
        var group = groups[key];
        groups[key].seats = Number(groups[key].seats);
    });
</script>