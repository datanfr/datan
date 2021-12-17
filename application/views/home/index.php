<!-- BLOC SEARCHING FOR A MP BLOC-HEMYCICLE-HOME -->
<div class="container-fluid bloc-hemycicle-home pg-home d-flex flex-column justify-content-around py-4 async_home" data-src="<?= asset_url() ?>imgs/cover/hemicycle-from-back.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-from-back-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-from-back-375.jpg">
  <div class="d-flex flex-column align-items-center">
    <h1 class="text-center">L'Assemblée nationale, enfin <span>compréhensible</span></h1>
  </div>
  <div class="row bloc-search-deputes mt-4">
    <div class="col-xl-6 col-lg-8 col-md-10 offset-xl-3 offset-lg-2 offset-md-1">
      <div class="card">
        <div class="card-header d-flex flex-row justify-content-center pt-4 pb-1">
          <h2>JE TROUVE MON DÉPUTÉ</h2>
        </div>
        <div class="card-header row pb-4">
          <div class="col-md-6 px-4">
            <div class="input-name pt-1">
              <form autocomplete="off">
                <div class="autocomplete">
                  <label class="pl-2" for="deputesNames">Son nom ?</label>
                  <input id="deputesNames" type="text" name="depute" placeholder='Exemple : <?= $depute_random['nameFirst'] .' ' . $depute_random['nameLast'] ?>'>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-6 px-4 mt-2 mt-md-0">
            <div class="input-commune pt-1">
              <form autocomplete="off">
                <div class="autocomplete" id="autocomplete">
                  <label class="pl-2" for="cities">Ma commune ?</label>
                  <input id="cities" type="text" name="cities" placeholder="Exemple : <?= $commune_random['commune_nom'] ?>">
                  <div id="citiesNamesautocomplete-list" class="autocomplete-items*">
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-4 d-flex flex-column align-items-center">
    <a href="<?= base_url();?>deputes" class="no-decoration">
      <button type="button" class="btn btn-outline-light">Tous les députés</button>
    </a>
  </div>
</div>
<!-- END SEARCHING FOR MP -->
<div class="container-fluid pg-home" id="container-always-fluid*">
  <!-- BLOC CONSTATS -->
  <div class="row bloc-constats py-4">
    <div class="container p-md-0">
      <div class="row py-4">
        <div class="col-12">
          <h2 class="text-center pb-4">Datan : un site de <b>vulgarisation parlementaire</b></h2>
          <div class="row pt-4">
            <!-- Constat -->
            <div class="d-flex align-items-start justify-content-center col-xl-5 col-md-6 col-12">
              <div>
                <h3 class="mb-3">Constat</h3>
                <p>Il est difficile de <b>suivre l'activité</b> des députés <br>et de <b>savoir ce qu'ils votent</b></p>
              </div>
            </div>
            <!-- Solution -->
            <div class="d-flex align-items-start justify-content-center col-md-6 col-xl-5 offset-xl-2 col-12">
              <div>
                <h3 class="mb-3">Solution</h3>
                <p>Datan <b>explique les votes</b> <br>des députés et groupes politiques.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- VOTES CARDS -->
  <div class="row bloc-votes" id="pattern_background">
    <div class="container p-md-0">
      <div class="row py-4">
        <div class="col-12">
          <h2 class="text-center my-4">Derniers votes décryptés par Datan</h2>
        </div>
        <div class="col-12 carousel-container bloc-carousel-votes-flickity">
          <?php $this->load->view('votes/partials/votes_carousel.php', array('votes' => $votes)) ?>
          <div class="carousel-buttons d-flex justify-content-center">
            <button type="button" class="btn prev mr-2 carousel--prev" aria-label="précédent">
              <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
            </button>
            <a class="btn see-all-carousel mx-2" href="<?= base_url() ?>votes/decryptes">
              <span>VOIR TOUS LES VOTES</span>
            </a>
            <button type="button" class="btn next ml-2 carousel--next" aria-label="suivant">
              <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- BLOC-HASARD -->
  <div class="row bloc-hasard">
    <div class="container pt-0">
      <div class="row py-5">
        <!-- DEPUTE AU HASARD CARD -->
        <div class="col-md-6 py-4">
          <h2>DÉPUTÉ<?= mb_strtoupper($depute_random['e']) ?> AU HASARD</h2>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_random, 'tag' => 'span', 'cat' => true)) ?>
          </div>
        </div>
        <!-- GROUPE AU HASARD CARD -->
        <div class="col-md-6 py-4">
          <h2>GROUPE AU HASARD</h2>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupe_random, 'tag' => 'span', 'active' => TRUE, 'cat' => true)) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- BLOC STATS -->
  <div class="row bloc-statistiques">
    <div class="container py-3">
      <div class="row pt-5 pb-4">
        <div class="col-12">
          <h2 class="text-center">Ces 12 derniers mois</h2>
        </div>
      </div>
      <div class="row pb-5">
        <!-- VOTE LE PLUS -->
        <div class="col-xl-3 col-md-6 py-4">
          <h3>VOTE LE <span class="plus">PLUS</span></h3>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_vote_plus, 'tag' => 'span', 'cat' => true)) ?>
          </div>
        </div>
        <!-- VOTE LE MOINS -->
        <div class="col-xl-3 col-md-6 py-4">
          <h3>VOTE LE <span class="minus">MOINS</span></h3>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_vote_moins, 'tag' => 'span', 'cat' => true)) ?>
          </div>
        </div>
        <!-- PLUS LOYAL -->
        <div class="col-xl-3 col-md-6 py-4">
          <h3><?= mb_strtoupper($depute_loyal_plus['le']) ?> <span class="plus">PLUS</span> LOYAL<?= mb_strtoupper($depute_loyal_plus['e']) ?></h3>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_loyal_plus, 'tag' => 'span', 'cat' => true)) ?>
          </div>
        </div>
        <!-- MOINS LOYAL -->
        <div class="col-xl-3 col-md-6 py-4">
          <h3><?= mb_strtoupper($depute_loyal_moins['le']) ?> <span class="minus">MOINS</span> LOYAL<?= mb_strtoupper($depute_loyal_moins['e']) ?></h3>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_loyal_moins, 'tag' => 'span', 'cat' => true)) ?>
          </div>
        </div>
      </div>
      <div class="pb-5 d-flex justify-content-center">
        <a href="<?= base_url();?>statistiques" class="no-decoration">
          <button type="button" class="btn btn-outline-primary">Découvrez nos statistiques</button>
        </a>
      </div>
    </div>
  </div> <!-- // END BLOC STATS -->
  <!-- BLOC ELECTION -->
  <div class="row bloc-election" id="pattern_background">
    <div class="container p-md-0">
      <div class="row py-4">
        <div class="col-12">
          <h2 class="text-center my-4">Députés candidats à l'élection présidentielle</h2>
        </div>
      </div>
      <div class="row pt-2 pb-5">
        <div class="col-md-7 d-flex flex-column justify-content-center">
          <p>L'élection présidentielle se déroulera les dimanches <b>10 et 24 avril 2022</b>.</p>
          <p>Un député peut se présenter à l'élection présidentielle. Il devra alors quitter son siège de député s'il est élu président.</p>
          <p>Au total, <span class="font-weight-bold text-primary"><?= $candidatsN ?> députés sont candidats</span> à l'élection présidentielle de 2022.</p>
        </div>
        <div class="col-md-5 mt-5 mt-md-0">
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $candidatRandom, 'tag' => 'span', 'cat' => true)) ?>
          </div>
        </div>
      </div>
      <div class="row pb-5">
        <div class="col-12 d-flex justify-content-center">
          <a href="<?= base_url();?>elections/presidentielle-2022" class="no-decoration">
            <button type="button" class="btn btn-primary">Découvrez les députés élus</button>
          </a>
        </div>
      </div>
    </div>
  </div> <!-- END BLOC ELECTIONS -->
  <!-- BLOC NEWSLETTER -->
  <div class="row bloc-newsletter">
    <div class="container py-5" >
      <div class="row py-3">
        <div class="col-12">
          <h2 class="text-center">Restez informé !</h2>
          <p class="mt-5 mb-0 text-center">N'hésitez pas à vous inscrire à notre newsletter !</p>
          <div class="text-center">
            <button class="btn btn-primary mt-5" data-toggle="modal" data-target="#newsletter">S'inscrire à la newsletter</button>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- NED BLOC NEWSLETTER -->
  <!-- BLOC ILS PARLENT DE NOUS -->
  <div class="row">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <h2 class="text-center my-4">Ils parlent de nous</h2>
        </div>
      </div>
      <div class="row pb-5">
        <div class="col-12 d-flex flex-wrap justify-content-around align-items-center">
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.leparisien.fr/info-paris-ile-de-france-oise/vote-assiduite-loyaute-comment-se-comportent-vos-deputes-franciliens-et-oisiens-21-01-2021-8420482.php") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="120" height="38" data-src="<?= asset_url() ?>imgs/media/le_parisien.png" alt="Le Parisien">
          </span>
          <a href="https://theconversation.com/reintroduction-des-pesticides-neonicotino-des-comment-nos-deputes-ont-ils-vote-et-pourquoi-155158" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="220" height="17" data-src="<?= asset_url() ?>imgs/media/the_conversation.png" alt="The Conversation">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.sudouest.fr/redaction/le-cercle-sud-ouest-des-idees/reintroduction-des-pesticides-neonicotinoides-comment-nos-deputes-ont-ils-vote-et-pourquoi-1400279.php") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="65" height="59" data-src="<?= asset_url() ?>imgs/media/sud_ouest.png" alt="Sud Ouest">
          </span>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.tv78.com/78-journal-info-yvelines-actu-edition-25-janvier-2021/") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="72" data-src="<?= asset_url() ?>imgs/media/tv78.png" alt="TV 78">
          </span>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.ladepeche.fr/2021/01/23/comment-votent-vos-deputes-a-lassemblee-nationale-9328626.php") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="37" data-src="<?= asset_url() ?>imgs/media/depeche.png" alt="La Dépêche Ouest">
          </span>
          <a href="https://www.latribune.fr/opinions/tribunes/apres-trois-votes-de-confiance-la-majorite-presidentielle-s-erode-t-elle-a-l-assemblee-nationale-853748.html" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="100" height="45" data-src="<?= asset_url() ?>imgs/media/la_tribune.png" alt="La Tribune">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://ram05.fr/podcasts/journal/29-novembre-2021-7") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="75" height="50" data-src="<?= asset_url() ?>imgs/media/ram05.png" alt="Radio Ram05">
          </span>
        </div>
      </div>
    </div>
  </div> <!-- // END BLOC ILS PARLENT DE NOUS -->
  <div class="row bloc-pie" id="pattern_background">
    <div class="container py-3">
      <div class="row pt-5">
        <div class="col-12">
          <h2 class="text-center">Composition de l'Assemblée nationale</h2>
        </div>
      </div>
      <div class="row pt-3">
        <div class="col-12">
          <p class="text-center">Découvrez le nombre de députés par groupe parlementaire.</p>
        </div>
      </div>
      <div class="row mt-5 mb-5">
        <div class="col-lg-7 d-flex justify-content-center align-items-center">
          <canvas id="chartHemycicle"></canvas>
        </div>
        <div class="col-lg-5 d-flex justify-content-center mt-5 mt-lg-0">
          <table class="tableGroupes">
            <tbody>
              <?php foreach ($groupes as $groupe): ?>
                <tr>
                  <td>
                    <div class="square" style="background-color: <?= $groupe['couleurAssociee'] ?>">
                    </div>
                  </td>
                  <td id="table<?= $groupe['libelleAbrev'] ?>">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>" class="no-decoration underline"><?= $groupe['libelle'] ?></a>
                  </td>
                  <td class="effectif"><?= $groupe['effectif'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- BLOC POSTS -->
  <div class="row">
    <div class="container p-md-0">
      <div class="row py-5">
        <div class="col-lg-12 bloc-posts">
          <h2 class="text-center">L'actualité du Parlement</h2>
          <div class="row pt-4">
            <?php foreach ($posts as $post): ?>
              <div class="col-12 col-md-6 offset-md-3 col-lg-12 offset-lg-0">
                <div class="card card-post my-3">
                  <div class="row no-gutters">
                    <div class="col-auto img-wrap d-none d-lg-block">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder.png" data-src="<?= asset_url() ?>imgs/posts/img_post_<?= $post['id'] ?>.png" alt="Image post <?= $post['id'] ?>">
                    </div>
                    <div class="col">
                      <div class="card-block p-3">
                        <span class="category mr-2"><?= mb_strtoupper($post['category_name']) ?></span>
                        <span class="date mr-2">Créé le <?= $post['created_at_fr'] ?></span>
                        <h2 class="card-title">
                          <a href="<?= base_url() ?>blog/<?= $post['category_slug'] ?>/<?= $post['slug'] ?>" class="stretched-link no-decoration"><?= $post['title'] ?></a>
                        </h2>
                        <p class="card-text"><?= word_limiter(Strip_tags($post['body']), 35) ?></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- // END BLOC POSTS -->
  <!-- FRANCE MAP -->
  <div class="row" id="pattern_background">
    <div class="container p-md-0">
      <div class="row py-5">
        <div class="col-lg-6 col-12 bloc-map mt-4 mt-lg-0 offset-lg-3">
          <h2 class="text-center pb-5">Explorer les députés par département</h2>
          <div class="map map_france mt-3">
            <?= file_get_contents(asset_url()."imgs/france_map/map.svg"); ?>
          </div>
          <div class="map map_outre_mer">
            <?= file_get_contents(asset_url()."imgs/france_map/outre-mer.svg"); ?>
          </div>
          <div class="mt-5 d-flex flex-column align-items-center">
            <a href="<?= base_url();?>index_departements" class="no-decoration">
              <button type="button" class="btn btn-primary">Voir tous les départements</button>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- // END MAP -->
</div>

<script type="text/javascript">

document.addEventListener('DOMContentLoaded', function(){

  Chart.plugins.unregister(ChartDataLabels);
  var libelles = [
    <?php
    foreach ($groupesSorted as $groupe) {
      echo '"'.$groupe["libelleAbrev"].'",';
    }
     ?>
  ];

  var data = {
      labels: [
        <?php
        foreach ($groupesSorted as $groupe) {
          echo '"'.$groupe["libelle"].' ('.$groupe['libelleAbrev'].')",';
        }
         ?>
      ],
      datasets: [
          {
              data: [
                <?php
                foreach ($groupesSorted as $groupe) {
                  echo $groupe["effectif"].",";
                }
                 ?>
              ],
              backgroundColor: [
                <?php
                foreach ($groupesSorted as $groupe) {
                  echo '"'.$groupe["couleurAssociee"].'",';
                }
                 ?>
              ],
              hoverBackgroundColor: [
                <?php
                foreach ($groupesSorted as $groupe) {
                  echo '"'.$groupe["couleurAssociee"].'",';
                }
                 ?>
              ]
          }]
  };

  var ctx = document.getElementById("chartHemycicle");

  // And for a doughnut chart
  var chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    rotation: 1 * Math.PI,
    circumference: 1 * Math.PI,
    legend: false,
    layout: {
      padding: 10
    },
    plugins: {
      datalabels: {
        anchor: "end",
        backgroundColor: function(context){
          return context.dataset.backgroundColor;
        },
        borderColor: "white",
        borderRadius: 25,
        borderWidth: 1,
        color: "white",
        font: {
          size: 10
        }
      }
    },
    onClick: function(e){
      if (screen.width >= 960) {
        var element = this.getElementsAtEvent(e);
        var idx = element[0]['_index'];
        var group = libelles[idx];
        location.href = 'https://datan.fr/groupes/' + group;
      }
    },
    hover: {
      onHover: function(x, y){
        const section = y[0];
        const currentStyle = x.target.style;
        currentStyle.cursor = section ? 'pointer' : 'default';
      }
    }
  }

  // Init the chart
  var pieChart = new Chart(ctx, {
    plugins: [
      ChartDataLabels,
      {
        beforeLayout: function(chart) {
          var showLabels = (chart.width) > 500 ? true : false;
          chart.options.plugins.datalabels.display = showLabels;
        }
      },
      {
        onresize: function(chart) {
          var showLabels = (chart.width) > 500 ? true : false;
          chart.options.plugins.datalabels.display = showLabels;
        }
      }
    ],
    type: 'doughnut',
    data: data,
    options: chartOptions,
  });

});

</script>
