<div class='container pg-outils pg-outils-labo my-5'>
    <div class='row'>
        <div class='col-lg-8 offset-lg-2'>
            <h1 class='text-center'>Formez votre coalition à l'Assemblée nationale</h1>
            <p class='mt-5 h'><span class='font-weight-bold text-primary'>289</span> : c'est le nombre de députés nécessaire pour obtenir la <span class='font-weight-bold'>majorité absolue</span> à l'Assemblée nationale.</p>
            <p>Après les <a href="<?= base_url() ?>elections/legislatives-2024">élections législatives de 2024</a>, aucun groupe politique n'a atteint ce nombre. Les groupes doivent donc <span class='font-weight-bold'>former des coalitions</span> pour arriver à faire voter des projets et propositions de loi.</p>
            <p>Essayez notre <span class='font-weight-bold'>simulateur de coalition</span>. Sélectionnez différents groupes de l'Assemblée nationale et découvrez si vous pouvez atteindre les 289 députés nécessaires pour une majorité absolue. Le simulateur utilise les données actualisées de la composition des groupes politiques.</p>
        </div>
        <div class='col-12 mt-5'>
            <div class='containerHemicycle d-flex justify-content-center'>
                <?= file_get_contents(asset_url() . '/imgs/hemycicle_position/hemicycle_empty_caseSensitive.svg') ?>
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
    <div class="row">
        <div class="col-12">
            <!-- ADD DONATION CAMPAIGN PARTIAL -->
            <?php $this->view('partials/campaign.php', array('wrapper_classes' => array('mt-5'))) ?>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-10 offset-lg-1">
            <h2 class="mt-5">Qu’est-ce que la majorité absolue à l’Assemblée nationale ?</h2>
            <p>La <b>majorité absolue à l'Assemblée nationale</b> est de <b>289 sièges</b>, soit plus de la moitié des 577 députés. Cette majorité permet à un gouvernement ou une coalition politique de voter ses projets et propositions de loi sans dépendre d'autres groupes parlementaires, garantissant une gouvernance stable.
            <p>Aux <a href="<?= base_url() ?>elections/legislatives-2022">élections législatives de 2022</a>, la coalition gouvernementale Renaissance n'a remporté que 245 sièges, bien en deçà de la majorité absolue requise. Cette situation s'est reproduite en <a href="<?= base_url() ?>elections/legislatives-2024">2024</a>, forçant le gouvernement à chercher des alliances avec d'autres groupes pour faire adopter chaque texte.</p> 
            <h2 class="mt-5">Majorité relative vs majorité absolue : quelle différence ?</h2>
            <p>Un groupe politique a la <b>majorité relative</b> si son nombre de voix est simplement supérieur aux autres groupes, sans pour autant atteindre la moitié des sièges du Parlement. Par exemple, un groupe avec 40% de sièges face à d'autres ayant 35% et 25% dispose d'une majorité relative.</p>
            <p>La <b>différence majorité relative vs majorité absolue</b> change la dynamique politique. Avec une majorité relative, l'exécutif doit négocier chaque vote et chercher des alliances avec d'autres groupes politiques à l'Assemblée, ralantissant l'adoption des lois et rendant l'action gouvernementale plus fragile. Avec une majorité absolue, le gouvernement vote ses projets de loi librement, garantissant plus de stabilité et une meilleure visibilité politique.</p>
            <p>Par exemple, après les <b>élections législatives de 2017</b>, la <a href="<?= base_url() ?>groupes/legislature-15/larem">République en Marche (LREM)</a> et le <a href="<?= base_url() ?>groupes/legislature-15/modem">Modem</a> disposaient de 361 sièges (majorité absolue). Les gouvernements d'Édouard Philippe et de Jean Castex ont pu gouverner sans contrainte parlementaire.</p>
            <h2 class="mt-5">Que se passe-t-il sans majorité absolue ?</h2>
            <p>Quand aucun groupe n'obtient la majorité absolue à l'Assemblée nationale, comme en 2022 et 2024, le <b>paysage politique se fragmente</b> et la gouvernance se complique. Le gouvernement doit alors conclure des alliances avec d'autres groupes pour faire voter ses lois. Cette situation crée une instabilité politique.</p>
            <p>Le Parlement devient alors un acteur central du jeu politique. Le pouvoir se déplace du gouvernement vers l'Assemblée nationale, où les compromis se négocient texte par texte. Cette situation redonne de l'influence aux groupes politiques et aux députés.</p>
            <p>En l'absence de coalition ou d'accord, le gouvernement peut recourir à des outils pour adopter des projets sans vote (article 49-3). Mais c'est risqué : une majorité de députés peut alors renverser le gouvernement par une <a href="<?= base_url() ?>blog/actualite-politique/la-censure-est-elle-de-retour-a-lassemblee-nationale">motion de censure</a>.</p>
            <h2 class="mt-5">Qu'est-ce qu'une coalition parlementaire ?</h2>
            <p>Une coalition est une <b>alliance entre plusieurs groupes politiques</b> qui s'unissent pour former une majorité à l'Assemblée nationale. En l'absence de majorité absolue pour un seul groupe, cette coalition permet de gouverner et faire adopter les lois. Une coalition peut être ponctuelle ou structurée, avec la présence d'un programme de coalition et la participation de chaque parti au gouvernement.</p>            
            <p>Si la France pratique peu les coalitions, elles sont <b>la norme dans de nombreux pays européens</b> comme l'Allemagne, la Belgique, les Pays-Bas, l'Italie ou au Parlement européen, où droite et gauche coopèrent depuis des décennies.</p>
            <p>En France, avec une culture du compromis moins ancrée, les coalitions stables et programmatiques sont rares. Depuis la perte de la majorité absolue en 2022 pour <a href="<?= base_url() ?>groupes/legislature-16/re">Renaissance</a>, les accords se font texte par texte, ce qui accentue l'instabilité gouvernementale.</p>
            <h2 class="mt-5">FAQ : Questions courantes sur l'absence de majorité absolue</h2>
            <?php foreach($faq[0] as $elmt): ?>
                <p><b><?= $elmt['title'] ?></b> <?= $elmt['text'] ?></p>
            <?php endforeach; ?>
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