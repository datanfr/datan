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
        <div class="<?= $groupe_random ? "col-md-6" : "col-md-12" ?> py-4">
          <h2>DÉPUTÉ<?= mb_strtoupper($depute_random['e']) ?> AU HASARD</h2>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_random, 'tag' => 'span', 'cat' => true, 'logo' => true, 'stats' => FALSE)) ?>
          </div>
        </div>
        <!-- GROUPE AU HASARD CARD -->
        <?php if ($groupe_random): ?>
          <div class="col-md-6 py-4">
            <h2>GROUPE AU HASARD</h2>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupe_random, 'tag' => 'span', 'active' => TRUE, 'cat' => $groupe_random['effectif'] . ' membres', 'stats' => FALSE)) ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <!-- BLOC EXPLICATIONS -->
  <div class="row bloc-votes" id="pattern_background">
    <div class="container p-md-0">
      <div class="row py-4">
        <div class="col-12">
          <h2 class="text-center my-4">Dernières explications de vote</h2>
        </div>
        <div class="col-8 offset-2 text-center">
          <p>Les députés peuvent sur Datan expliquer leurs positions de vote. Pourquoi ont-ils voté pour ou contre un texte ? Découvrez les dernières explications !</p>
        </div>
        <div class="col-12 mt-4 mb-4 d-flex flex-md-row flex-column">
          <?php foreach ($explications as $key => $value): ?>
            <div class="card card-explication-home mx-md-2 mx-md-0 mb-4">
              <div class="card-header py-2">
                <div class="d-flex flex-row align-items-center">
                  <a href="<?= base_url() ?>deputes/<?= $value['dptSlug'] ?>/depute_<?= $value['nameUrl'] ?>">
                    <div class="depute-img-circle depute-img-circle-explication mr-3">
                      <picture>
                        <source srcset="<?= asset_url() ?>imgs/deputes_nobg_webp/depute_<?= $value['idImage'] ?>_webp.webp" type="image/webp">
                        <source srcset="<?= asset_url() ?>imgs/deputes_nobg/depute_<?= $value['idImage'] ?>.png" type="image/png">
                        <img src="<?= asset_url() ?>imgs/deputes_original/depute_<?= $value['idImage'] ?>.png" width="150" height="192" alt="Photo du député">
                      </picture>
                    </div>
                  </a>
                  <p class="title mb-0">
                    <a class="no-decoration underline" href="<?= base_url() ?>deputes/<?= $value['dptSlug'] ?>/depute_<?= $value['nameUrl'] ?>"><?= $value['nameFirst'] ?> <?= $value['nameLast'] ?></a> -
                    <a class="no decoration underline" href="<?= base_url() ?>groupes/legislature-<?= $value['legislature'] ?>/<?= mb_strtolower($value['libelleAbrev']) ?>"><span class="font-weight-bold" style="color: <?= $value['couleurAssociee'] ?>"><?= $value['libelleAbrev'] ?></span></a>
                  </p>
                </div>
              </div>
              <div class="card-body py-3">
                <p class="mb-0 font-italic font-weight-bold"><a class="no-decoration underline" href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>"><?=  $value['title']?></a></p>
                <p class="badge badge-<?= $value['vote'] ?> mb-4"><?= mb_strtoupper($value['vote']) ?></p>
                <p class="quoted mb-0 p-0"><?= word_limiter($value['text'], 20) ?></p>
              </div>
              <div class="card-footer pt-0 d-flex justify-content-center align-items-center">
                <a class="btn btn-primary text-white" role="button" href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>/explication_<?= $value['mpId'] ?>">
                  Lire plus
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <!-- BLOC STATS -->
  <?php if ($stats): ?>
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
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_vote_plus, 'tag' => 'span', 'cat' => true, 'logo' => true, 'stat' => round($depute_vote_plus['score']) . ' %')) ?>
            </div>
          </div>
          <!-- VOTE LE MOINS -->
          <div class="col-xl-3 col-md-6 py-4">
            <h3>VOTE LE <span class="minus">MOINS</span></h3>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_vote_moins, 'tag' => 'span', 'cat' => true, 'logo' => true, 'stat' => round($depute_vote_moins['score']) . ' %')) ?>
            </div>
          </div>
          <!-- PLUS LOYAL -->
          <div class="col-xl-3 col-md-6 py-4">
            <h3><?= mb_strtoupper($depute_loyal_plus['le']) ?> <span class="plus">PLUS</span> LOYAL<?= mb_strtoupper($depute_loyal_plus['e']) ?></h3>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_loyal_plus, 'tag' => 'span', 'cat' => true, 'logo' => true, 'stat' => round($depute_loyal_plus['score']) . ' %')) ?>
            </div>
          </div>
          <!-- MOINS LOYAL -->
          <div class="col-xl-3 col-md-6 py-4">
            <h3><?= mb_strtoupper($depute_loyal_moins['le']) ?> <span class="minus">MOINS</span> LOYAL<?= mb_strtoupper($depute_loyal_moins['e']) ?></h3>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_loyal_moins, 'tag' => 'span', 'cat' => true, 'logo' => true, 'stat' => round($depute_loyal_moins['score']) . ' %')) ?>
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
  <?php endif; ?>
  <!-- BLOC NEWSLETTER -->
  <div class="row bloc-newsletter" id="pattern_background">
    <div class="container py-5" >
      <div class="row py-3">
        <div class="col-lg-8 offset-lg-2">
          <h2 class="text-center">Restez informés !</h2>
          <p class="mt-5 text-center">Retrouvez dans notre newsletter mensuelle un condensé des derniers scrutins de l'Assemblée nationale et des positions des différents groupes politiques.
          <p class="text-center mb-0">Nous vous tiendrons également informé des dernières nouveautés du site internet Datan.</p>
          <div class="text-center">
            <button class="btn btn-primary mt-5" data-toggle="modal" data-target="#newsletter">Inscrivez-vous à la newsletter</button>
          </div>
        </div>
      </div>
    </div>
  </div> <!-- NED BLOC NEWSLETTER -->
  <div class="row bloc-support">
    <div class="container py-5">
      <div class="row mb-5">
        <div class="col-12">
          <h2 class="text-white text-center"> Quels groupes politiques soutiennent le gouvernement ?</h2>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6 col-md-5 d-flex align-items-center text-white">
          <div>
            <p>À l'Assemblée nationale, certains groupes sont membres de la majorité tandis que d'autres sont des groupes d'opposition.</p>
            <p>Les groupes de la majorité soutiennent l'action du gouvernement et votent en faveur des textes qu'il propose. L'opposition, si elle peut parfois soutenir le gouvernement, vote plus souvent contre les projets du gouvernement.</p>
            <p>Le gouvernement actuel est dirigé par la Première ministre Élisabeth Borne.</p>
            <p>Le groupe <?= $support_opposition['libelle'] ?> (<?= $support_opposition['libelleAbrev'] ?>) est le groupe d'opposition votant le plus souvent en faveur du gouvernement.</p>
          </div>
        </div>
        <div class="col-lg-6 col-md-7 mt-md-0 mt-4">
          <?php $max = max(array_column($support, 'value')) ?>
          <?php $maxVotes = max(array_column($support, 'votes')) ?>
          <div class="card card-statistiques border-0">
            <div class="card-body pb-0">
              <h3 class="text-center font-weight-bold h5">Nombre de textes du gouvernement votés par les groupes</h3>
              <p class="mt-3">Depuis le début de la législature, en 2022, il y a eu <b><?= $maxVotes ?> votes</b> sur des projets de loi du gouvernement. Découvrez les groupes politiques qui votent le plus souvent en faveur de ces textes.</p>
              <div class="legend d-flex justify-content-center">
                <div class="d-flex justify-content-center align-items-center mx-3">
                  <div class="legendBox" style="background-color: #5199C4"></div>
                  <span class="ml-2 font-italic">Majorité</span>
                </div>
                <div class="d-flex justify-content-center align-items-center mx-3">
                  <div class="legendBox" style="background-color: #EA9A27"></div>
                  <span class="ml-2 font-italic">Opposition</span>
                </div>
              </div>
              <div class="row mt-1 bar-container stats rounded-bottom py-4 mt-3">
                <div class="col-12">
                  <div class="chart">
                    <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
                      <?php foreach ($support as $key => $value): ?>
                        <div class="bars mx-1 mx-md-2" style="height: <?= round($value['value'] / $max * 100) ?>%; background-color: <?= $value['positionPolitique'] == 'Opposition' ? '#EA9A27' : '#5199C4' ?>">
                          <span class="score" style="font-size: 0.85rem"><?= $value['value'] ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <div class="col-12 d-flex justify-content-between mt-2">
                  <?php foreach ($support as $key => $value): ?>
                    <div class="legend-element d-flex align-items-center justify-content-center">
                      <span class="font-weight-bold text-center tooltipHelp tooltipDashed" data-toggle="tooltip" data-placement="top" title="<?= $value['libelle'] ?>"><?= remove_nupes($value['libelleAbrev']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div> <!-- END CARD GOVERNMENT -->
        </div>
      </div>
    </div>
  </div>
  <!-- BLOC ILS PARLENT DE NOUS -->
  <div class="row">
    <div class="container py-5">
      <div class="row pt-3">
        <div class="col-12">
          <h2 class="text-center">Ils parlent de nous</h2>
        </div>
      </div>
      <div class="row mt-5 pb-5">
        <div class="col-lg-8 offset-lg-2 d-flex flex-wrap justify-content-around align-items-center">
          <a href="https://www.lemonde.fr/politique/article/2023/06/23/le-rn-premier-opposant-a-emmanuel-macron-mais-soutien-regulier-a-l-assemblee-nationale_6178892_823448.html" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="35" data-src="<?= asset_url() ?>imgs/media/le_monde.png" alt="Le Monde">
          </a>
          <a href="https://www.lejdd.fr/Politique/interventions-textes-qui-sont-les-deputes-les-plus-actifs-de-ce-debut-de-legislature-4126439" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="35" data-src="<?= asset_url() ?>imgs/media/jdd.png" alt="JDD">
          </a>
          <a href="https://www.bfmtv.com/politique/infographie-assemblee-nationale-qui-sont-les-groupes-parlementaires-les-plus-proches_AV-202207260568.html" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="80" height="50" data-src="<?= asset_url() ?>imgs/media/bfm.jpg" alt="BFM TV">
          </a>
          <a href="https://www.leparisien.fr/politique/lr-a-lassemblee-nationale-radiographie-dun-groupe-indiscipline-23-10-2022-7PDSHRSJ5ZHC5OTRLUGHDCPVQY.php" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="120" height="38" data-src="<?= asset_url() ?>imgs/media/le_parisien.png" alt="Le Parisien">
          </a>
          <a href="https://www.liberation.fr/politique/aurelien-pradie-un-depute-et-conseiller-regional-tres-absent-20221202_M35E54RK2FFEFCGZGR5N6FRYII/" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="55" data-src="<?= asset_url() ?>imgs/media/liberation.png" alt="Libération">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.franceculture.fr/emissions/les-enjeux-des-reseaux-sociaux/la-lutte-contre-l-abstention-passe-par-les-reseaux") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="70" height="70" data-src="<?= asset_url() ?>imgs/media/france_culture.png" alt="France Culture">
          </span>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.ouest-france.fr/elections/legislatives/lannion-paimpol-quel-bilan-pour-le-depute-eric-bothorel-0f349264-e100-11ec-8469-165462e4a30b") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="120" height="38" data-src="<?= asset_url() ?>imgs/media/ouest_france.png" alt="Ouest France">
          </span>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.la-croix.com/France/lhemicycle-Rassemblement-national-attend-heure-2023-06-01-1201269583") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="30" data-src="<?= asset_url() ?>imgs/media/la_croix.png" alt="La Croix">
          </span>
          <a href="https://france3-regions.francetvinfo.fr/occitanie/haute-garonne/toulouse/legislatives-2022-top-5-de-l-activite-a-l-assemblee-il-a-fait-quoi-mon-depute-pendant-5-ans-2552692.html" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="136" height="26" data-src="<?= asset_url() ?>imgs/media/france3.png" alt="France 3">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.sudouest.fr/pyrenees-atlantiques/pau/assemblee-nationale-le-rn-pointe-le-manque-d-assiduite-de-david-habib-il-repond-qu-il-y-a-trop-d-amendements-futiles-12739495.php") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="65" height="59" data-src="<?= asset_url() ?>imgs/media/sud_ouest.png" alt="Sud Ouest">
          </span>
          <a href="https://www.capital.fr/economie-politique/classement-les-deputes-les-plus-bosseurs-et-ceux-qui-en-font-le-moins-1438612" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="122" height="40" data-src="<?= asset_url() ?>imgs/media/capital.png" alt="Capital">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.lepoint.fr/politique/budget-pourquoi-lr-fait-l-assemblee-buissonniere-20-10-2022-2494617_20.php") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="144" height="34" data-src="<?= asset_url() ?>imgs/media/le-point.png" alt="Le Point">
          </span>
          <a href="https://www.20minutes.fr/politique/assemblee_nationale/4042426-20230623-assemblee-nationale-rn-veut-poursuivre-lente-marche-vers-normalisation" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="70" height="70" data-src="<?= asset_url() ?>imgs/media/20_minutes.png" alt="20 Minutes">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.ladepeche.fr/2021/01/23/comment-votent-vos-deputes-a-lassemblee-nationale-9328626.php") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="37" data-src="<?= asset_url() ?>imgs/media/depeche.png" alt="La Dépêche Ouest">
          </span>
          <a href="https://www.latribune.fr/opinions/tribunes/apres-trois-votes-de-confiance-la-majorite-presidentielle-s-erode-t-elle-a-l-assemblee-nationale-853748.html" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="100" height="45" data-src="<?= asset_url() ?>imgs/media/la_tribune.png" alt="La Tribune">
          </a>
          <a href="https://www.letelegramme.fr/finistere/morlaix/legislatives-a-morlaix-quel-est-le-bilan-de-sandrine-le-feur-08-06-2022-13061024.php" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="135" height="19" data-src="<?= asset_url() ?>imgs/media/telegramme.png" alt="Le Télégramme">
          </a>
          <a href="https://theconversation.com/reintroduction-des-pesticides-neonicotino-des-comment-nos-deputes-ont-ils-vote-et-pourquoi-155158" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="220" height="17" data-src="<?= asset_url() ?>imgs/media/the_conversation.png" alt="The Conversation">
          </a>
          <a href="https://www.marianne.net/politique/lrem/benjamin-griveaux-agnes-buzyn-cedric-villani-ces-six-espoirs-evapores-de-la-macronie-saison-1" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="120" height="40" data-src="<?= asset_url() ?>imgs/media/marianne.png" alt="Marianne">
          </a>
          <a href="https://tnova.fr/democratie/politique-institutions/eric-ciotti-dans-ses-oeuvres/" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="119" height="71" data-src="<?= asset_url() ?>imgs/media/terra-nova.png" alt="Terra Nova">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://abonne.lunion.fr/id371931/article/2022-05-16/legislatives-le-bilan-de-la-deputee-valerie-beauvais-la-loupe") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="119" height="33" data-src="<?= asset_url() ?>imgs/media/lunion.png" alt="L'Union">
          </span>
          <a href="https://www.lepopulaire.fr/limoges-87000/actualites/desenclavement-emploi-gilets-jaunes-montee-du-rn-quel-bilan-pour-les-deputes-lrem-de-haute-vienne_14127678/" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="120" height="36" data-src="<?= asset_url() ?>imgs/media/populaire-centre.png" alt="Le Populaire du Centre">
          </a>
          <a href="https://www.petitbleu.fr/2022/06/06/legislatives-en-lot-et-garonne-michel-lauzzana-un-bilan-deux-satisfactions-et-deux-regrets-10341292.php" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="129" height="34" data-src="<?= asset_url() ?>imgs/media/petit-bleu.png" alt="Le Peitit bleu d'Agen">
          </a>
          <a href="https://www.lamontagne.fr/clermont-ferrand-63000/actualites/a-la-veille-des-legislatives-quel-bilan-pour-les-cinq-deputes-sortants-du-puy-de-dome_14137540/" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="148" height="17" data-src="<?= asset_url() ?>imgs/media/montagne.png" alt="La Montagne">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.contrepoints.org/2022/10/28/441739-motion-de-censure-un-coup-declat-tout-sauf-historique") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="128" height="21" data-src="<?= asset_url() ?>imgs/media/contrepoints.png" alt="Contrepoints">
          </span>
          <a href="https://www.radioevasion.net/2022/10/24/datan-vous-permet-de-suivre-de-pres-votre-deputee-ou-depute/" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="70" height="70" data-src="<?= asset_url() ?>imgs/media/radio-evasion.png" alt="Radio Evasion">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.tv78.com/78-journal-info-yvelines-actu-edition-25-janvier-2021/") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="150" height="72" data-src="<?= asset_url() ?>imgs/media/tv78.png" alt="TV 78">
          </span>
          <a href="https://www.laprovence.com/article/legislatives-2022/6788904/legislatives-2022-un-quiz-pour-savoir-si-vous-avez-les-memes-convictions-que-votre-depute.html" target="_blank" rel="noopener">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="183" height="28" data-src="<?= asset_url() ?>imgs/media/la_provence.png" alt="La Provence">
          </a>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.lest-eclair.fr/id498781/article/2023-06-30/un-apres-les-legislatives-les-republicains-deboussoles") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="145" height="22" data-src="<?= asset_url() ?>imgs/media/est_eclair.png" alt="L'Est eclair">
          </span>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.lemondedesados.fr/elections-legislatives-a-quoi-servent-les-deputes/") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="85" height="40" data-src="<?= asset_url() ?>imgs/media/mda.png" alt="Le Monde des Ados">
          </span>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://www.republicain-lorrain.fr/politique/2023/02/02/etre-depute-c-est-du-temps-plein") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="123" height="40" data-src="<?= asset_url() ?>imgs/media/republicain_lorrain.png" alt="Républicain Lorrain">
          </span>
          <span class="url_obf" url_obf="<?= url_obfuscation("https://ram05.fr/podcasts/journal/29-novembre-2021-7") ?>">
            <img class="mx-2 my-3 img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="75" height="50" data-src="<?= asset_url() ?>imgs/media/ram05.png" alt="Radio Ram05">
          </span>
        </div>
      </div>
    </div>
  </div> <!-- // END BLOC ILS PARLENT DE NOUS -->
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
                      <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>" class="no-decoration underline"><?= name_group($groupe['libelle']) ?></a>
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
  <?php endif; ?>
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
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder.png" data-src="<?= asset_url() ?>imgs/posts/img_post_<?= $post['id'] ?>.png" alt="Image post <?= $post['id'] ?>" width="480" height="240">
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
          <h2 class="text-center pb-5">Explorez les députés par département</h2>
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

  Chart.register(ChartDataLabels);
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
          echo '"'.name_group($groupe["libelle"]).' ('.$groupe['libelleAbrev'].')",';
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
    circumference: 180,
    rotation: 270,
    layout:{
      padding: 15
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
      },
      legend: {
        display: false
      },
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
