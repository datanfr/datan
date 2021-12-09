    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="position: relative">
      <?php if ($vote['edited']): ?>
        <div class="container pg-vote-individual d-none d-md-flex flex-column justify-content-end">
          <div class="row">
            <div class="col-md-4 offset-md-8 text-right">
              <a class="category underline" href="<?= base_url() ?>votes/decryptes/<?= $vote['category_slug'] ?>"># <?= ($vote['category']) ?></a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <div class="container pg-vote-individual">
      <div class="row">
        <div class="col-md-8 col-12 bloc-card-vote">
          <div class="card card-vote-infos">
            <div class="card-body">
              <span class="num">VOTE n° <?= $vote['voteNumero'] ?></span>
              <?php if ($vote['title'] != "" & $vote['state'] == "published"): ?>
                <h1 class="title"><?= ucfirst($vote['title']) ?></h1>
                <?php else: ?>
                <h1 class="title"><?= ucfirst($vote['titre']) ?></h1>
              <?php endif; ?>
              <span class="badge sort badge-<?= $vote['sortCode'] ?> mt-1"><?= mb_strtoupper($vote['sortCode']) ?></span>
            </div>
            <div class="d-flex results">
              <div class="sort-bg-pour">
                <span class="legend">POUR</span>
                <span class="number"><?= $vote['pour'] ?></span>
              </div>
              <div  style="background-color: #FFAD29">
                <span class="legend">ABSTENTION</span>
                <span class="number"><?= $vote['abstention'] ?></span>
              </div>
              <div style="background-color: #C5283D">
                <span class="legend">CONTRE</span>
                <span class="number"><?= $vote['contre'] ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-lg-8 col-md-8 col-12">
          <div>
            <h2>Résultat du vote</h2>
            <div class="row">
              <div class="col-lg-8">
                <p class="mt-4">Les députés ont <span class="font-weight-bold sort-<?= $vote['sortCode'] ?>"><?= $vote['sortCode'] ?></span> le <?= $vote['date_edited'] ?> <?= $vote['titre'] ?></p>
                <?php if ($vote['sortCode'] == "adopté"): ?>
                  <p>Au total, <b><?= $vote['nombreVotants'] ?> députés</b> ont pris part au vote : <?= round($vote['pour_pct']) ?> % ont voté en faveur, <?= round($vote['contre_pct']) ?> % ont voté contre, et <?= round($vote['abs_pct']) ?> % se sont abstenus.</p>
                  <?php else: ?>
                    <p>Au total, <b><?= $vote['nombreVotants'] ?> députés</b> ont pris part au vote : <?= round($vote['contre_pct']) ?> % ont voté contre, <?= round($vote['pour_pct']) ?> % ont voté en faveur, et <?= round($vote['abs_pct']) ?> % se sont abstenus.</p>
                <?php endif; ?>
              </div>
              <div class="col-lg-4">
                <div class="d-flex justify-content-center mt-3">
                  <div style="width: 175px">
                    <canvas id="pie-chart" width="100px" height="100px"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bloc-infos d-md-none mt-5 mt-md-0">
            <h2>Infos</h2>
            <table class="infos">
              <tbody>
                <tr>
                  <td class="icon"><?= file_get_contents(base_url().'/assets/imgs/icons/calendar.svg') ?></td>
                  <td class="label d-none d-lg-table-cell">Date</td>
                  <td class="value"><?= $vote['date_edited'] ?></td>
                </tr>
                <tr>
                  <td class="icon"><?= file_get_contents(base_url().'/assets/imgs/icons/journal.svg') ?></td>
                  <td class="label d-none d-lg-table-cell">Type de vote</td>
                  <td class="value">
                    <?= ucfirst($vote['type_edited']) ?>
                    <a class="ml-1" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="question no-decoration" aria-label="Tooltip explication" data-content="<?= $vote['type_edited_explication'] ?>"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                  </td>
                </tr>
                <?php if ($vote['dossier_titre'] != ""): ?>
                  <tr>
                    <td class="icon"><?= file_get_contents(base_url().'/assets/imgs/icons/folder.svg') ?></td>
                    <td class="label d-none d-lg-table-cell">Dossier</td>
                    <td class="value"><?= $vote['dossier_titre'] ?></td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="mt-5">
            <h2>La position des groupes</h2>
            <div class="mt-4 bloc-groupes d-flex flex-column flex-lg-row">
              <!-- POUR -->
              <?php if (in_array_r("pour", $groupes)): ?>
                <div class="bloc-groupe d-flex justify-content-center">
                  <div class="card m-2" style="border: 0.5px solid rgba(0, 183, 148, 1)">
                    <div class="thumb d-flex justify-content-end">
                      <span style="background-color: rgba(0, 183, 148, 1);">
                        POUR
                      </span>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-around flex-wrap">
                      <?php foreach ($groupes as $groupe): ?>
                        <?php if ($groupe['positionMajoritaire'] == 'pour'): ?>
                          <div class="card-vote-groupe my-1 mx-1" data-toggle="tooltip" data-placement="top" title="<?= $groupe['libelle'] ?> (<?= $groupe['libelleAbrev'] ?>)">
                            <picture>
                              <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
                              <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
                              <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groupe['libelle'] ?>">
                            </picture>
                          </div>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <!-- CONTRE -->
              <?php if (in_array_r("contre", $groupes)): ?>
                <div class="bloc-groupe d-flex justify-content-center">
                  <div class="card m-2" style="border: 0.5px solid rgba(197, 40, 61, 1)">
                    <div class="thumb d-flex justify-content-end">
                      <span style="background-color: rgba(197, 40, 61, 1)">
                        CONTRE
                      </span>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-around flex-wrap">
                      <?php foreach ($groupes as $groupe): ?>
                        <?php if ($groupe['positionMajoritaire'] == 'contre'): ?>
                          <div class="card-vote-groupe my-1 mx-1" data-toggle="tooltip" data-placement="top" title="<?= $groupe['libelle'] ?> (<?= $groupe['libelleAbrev'] ?>)">
                            <picture>
                              <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
                              <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
                              <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groupe['libelle'] ?>">
                            </picture>
                          </div>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($description): ?>
            <div class="mt-5">
              <h2>Pour mieux comprendre</h2>
              <div class="card mt-4">
                <div class="card-body">
                  <div class="read-more-container">
                    <?= $vote['description'] ?>
                    <p class="read-more-button"><a href="#" class="btn btn-primary">En savoir plus</a></p>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="mt-5">
              <h2>Ce vote n'est pas compréhensible ?</h2>
              <p class="mt-4">Certains votes peuvent être compliqués à comprendre. Comment savoir à quoi correspond un article dans un projet de loi ? Comment connaître le contenu de tel amendement ?</p>
              <p>Pas de problème, <b>l’équipe de Datan contextualise et simplifie certains votes</b>.</p>
              <p>Vous souhaitez que l'on vous explique ce vote ? Demandez-nous-le !</p>
              <div class="d-flex justify-content-center mt-4">
                <a class="btn bg-primary text-white font-weight-bold cursor-pointer" data-toggle="modal" data-target="#voteDatanRequested">Demandez-nous une explication !</a>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-lg-4 col-md-4 col-12">
          <div class="bloc-infos d-none d-md-block mt-5 mt-md-0">
            <h2>Infos</h2>
            <table class="infos">
              <tbody>
                <tr>
                  <td class="icon"><?= file_get_contents(base_url().'/assets/imgs/icons/calendar.svg') ?></td>
                  <td class="label d-none d-lg-table-cell">Date</td>
                  <td class="value"><?= $vote['date_edited'] ?></td>
                </tr>
                <tr>
                  <td class="icon"><?= file_get_contents(base_url().'/assets/imgs/icons/journal.svg') ?></td>
                  <td class="label d-none d-lg-table-cell">Type de vote</td>
                  <td class="value">
                    <?= ucfirst($vote['type_edited']) ?>
                    <a class="ml-1" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="question no-decoration" aria-label="Tooltip explication" data-content="<?= $vote['type_edited_explication'] ?>"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                  </td>
                </tr>
                <?php if ($vote['dossier_titre'] != ""): ?>
                  <tr>
                    <td class="icon"><?= file_get_contents(base_url().'/assets/imgs/icons/folder.svg') ?></td>
                    <td class="label d-none d-lg-table-cell">Dossier</td>
                    <td class="value"><?= $vote['dossier_titre'] ?></td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="bloc-savoir-plus d-none d-md-block mt-5">
            <h3 class="subtitle">En savoir plus</h3>
            <div class="bloc-links">
              <div class="d-flex flex-column flex-lg-row">
                <?php if ($vote['dossierUrl']): ?>
                  <span class="d-flex justify-content-center align-items-center url_obf btn btn-secondary link my-1 my-lg-0 mx-lg-1" url_obf="<?= url_obfuscation($vote['dossierUrl']) ?>">
                    <span class="text">Le dossier</span>
                  </span>
                <?php endif; ?>
                <?php if ($vote['voteType'] == 'amendement' && !empty($documentLegislatif)): ?>
                  <span class="d-flex justify-content-center align-items-center url_obf btn btn-secondary link my-1 my-lg-0 mx-lg-1" url_obf="<?= url_obfuscation("https://www.assemblee-nationale.fr/dyn/" . $amdt['legislature'] . "/amendements/" . $documentLegislatif['numNotice'] ."/AN/".$amdt['numOrdre']) ?>">
                    <span class="text">L'amendement</span>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="bloc-social d-none d-md-block mt-5">
            <h3 class="subtitle">Partagez ce vote</h3>
            <?php $this->load->view('partials/share.php') ?>
          </div>
          <div class="bloc-nos-lois d-none d-md-block mt-5">
            <h3 class="subtitle">Prenez position</h3>
            <p>Vous aimez Datan ? Alors vous aimerez sûrement <b>Nos Lois</b>, une application permettant de donner son avis sur les textes en discussion à l'Assemblée.</p>
            <div class="d-flex justify-content-center">
              <div class="card align-items-center link nos-lois url_obf my-2 p-2" url_obf="<?= url_obfuscation("https://www.noslois.fr/") ?>" >
                <img src="<?= asset_url() ?>imgs/logos/nos-lois.png" alt="Logo Nos Lois">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php if (!empty($author)): ?>
      <div class="container-fluid pg-vote-individual bloc-author mt-5 py-4">
        <div class="container">
          <div class="row">
            <?php if (in_array($authorMeta['type'], array("mp", "gvt")) || ($authorMeta['type'] == "mps" && count($author) == 1)): ?>
              <div class="col-lg-7 d-flex flex-column justify-content-center">
                <?php if ($authorMeta['title'] == "amendement"): ?>
                  <h2 class="title text-center text-lg-left">L'auteur<?= !empty($author['civ']) ? gender($author['civ'])['e'] : "" ?> de l'amendement</h2>
                <?php elseif ($authorMeta['title'] == "rapporteur"): ?>
                  <h2 class="title text-center text-lg-left"><?= ucfirst(gender($author[0]['civ'])['le']) ?> rapporteur<?= gender($author[0]['civ'])['e'] ?></h2>
                <?php elseif ($authorMeta['title'] == "proposition"): ?>
                  <h2 class="title text-center text-lg-left">L'auteur<?= !empty($author[0]['civ']) ? gender($author[0]['civ'])['e'] : "" ?> de la proposition de loi</h2>
                <?php endif; ?>
                <p class="text-center text-lg-left"><?= $authorMeta['explication'] ?></p>
              </div>
              <?php if (in_array($authorMeta['type'], array("mp", "mps"))): ?>
                <?php $author = $authorMeta['type'] == "mps" ? $author[0] : $author ?>
                <div class="col-lg-5 d-flex align-items-center justify-content-center pb-4">
                  <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $author, 'tag' => 'h3', 'cat' => false)) ?>
                </div>
              <?php else: ?>
                <div class="col-lg-5 d-flex align-items-center justify-content-center pb-4">
                  <div class="card card-depute mx-2">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                      <div>
                        <h3 class="d-block card-title">Gouvernement <?= ucfirst(mb_strtolower($author['libelleAbrege'])) ?></h3>
                        <span class="d-block">Formé le <?= $author['dateDebutFR'] ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="col-12 d-flex flex-column align-items-center">
              <?php if ($authorMeta['title'] == "rapporteur"): ?>
                <h2 class="title">Les rapporteurs</h2>
              <?php elseif($authorMeta['title'] == "proposition"): ?>
                <h2 class="title">Les auteurs de la proposition de loi</h2>
                <?php endif; ?>
                <p class="text-center"><?= $authorMeta['explication'] ?></p>
              </div>
              <div class="col-12 carousel-container">
                <div class="carousel-cards">
                  <?php foreach ($author as $x) : ?>
                    <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $x, 'tag' => 'h3', 'cat' => false)) ?>
                  <?php endforeach; ?>
                </div>
                <div class="carousel-buttons d-flex justify-content-center mb-2">
                  <button type="button" class="btn prev mr-2 carousel--prev" aria-label="précédent">
                    <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
                  </button>
                  <button type="button" class="btn next ml-2 carousel--next" aria-label="suivant">
                    <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
                  </button>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div class="container pg-vote-individual">
      <div class="row row mt-5">
        <div class="col-12">
          <div class="bloc-table">
            <h2>Les votes des députés et des groupes</h2>
            <nav class="mt-4">
              <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a class="nav-item nav-link active no-decoration" id="nav-groupes-tab" data-toggle="tab" href="#nav-groupes" role="tab" aria-controls="nav-groupes" aria-selected="true">
                  <h3>Groupes</h3>
                </a>
                <a class="nav-item nav-link no-decoration" id="nav-deputes-tab" data-toggle="tab" href="#nav-deputes" role="tab" aria-controls="nav-deputes" aria-selected="false">
                  <h3>Députés</h3>
                </a>
              </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
              <div class="tab-pane fade show active" id="nav-groupes" role="tabpanel" aria-labelledby="nav-groupes-tab">
                <table class="table table-striped" id="table-vote-individual-groupes" style="width: 100%">
                  <thead>
                    <tr>
                      <th class="all">Groupe</th>
                      <th class="text-center all">Vote</th>
                      <th class="text-center min-tablet">Pour</th>
                      <th class="text-center min-tablet">Abstention</th>
                      <th class="text-center min-tablet">Contre</th>
                      <th class="text-center">Participation</th>
                      <th class="text-center min-tablet">
                        Cohésion
                        <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Taux de cohésion" aria-label="Tooltip cohésion" data-content="Le taux de cohésion représente <b>l'unité d'un groupe politique</b> lorsqu'il vote. Il peut prendre des mesures allant de 0 à 1. Un taux proche de 1 signifie que le groupe est très uni.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les mesures de cohésion des autres groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques#cohesion' target='_blank'>cliquez ici</a>."><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                      </th>
                      <th class="pl-4"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($groupes as $v_groupe): ?>
                      <tr>
                        <td class="name">
                          <a href="<?= base_url() ?>groupes/<?= mb_strtolower($v_groupe['libelleAbrev']) ?>" target="_blank" class="no-decoration underline">
                            <?= $v_groupe['libelle'] ?> (<?= $v_groupe['libelleAbrev'] ?>)
                          </a>
                        </td>
                        <td class="text-center vote sort-<?= $v_groupe['positionMajoritaire'] ?>">
                          <?php if ($v_groupe['positionMajoritaire'] != "nv"): ?>
                            <?= mb_strtoupper($v_groupe['positionMajoritaire']) ?>
                            <?php else: ?>
                            Non votant
                          <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $v_groupe['nombrePours'] ?></td>
                        <td class="text-center"><?= $v_groupe['nombreAbstentions'] ?></td>
                        <td class="text-center"><?= $v_groupe['nombreContres'] ?></td>
                        <td class="text-center"><?= $v_groupe['percentageVotants'] ?> %</td>
                        <td class="text-center"><?= $v_groupe['cohesion'] ?></td>
                        <td></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="tab-pane fade pt-3" id="nav-deputes" role="tabpanel" aria-labelledby="nav-deputes-tab">
                <table class="table table-striped table-vote-individual" id="table-vote-individual-deputes" style="width: 100%">
                  <thead>
                    <tr>
                      <th class="all" style="vertical-align: top">Député</th>
                      <th class="text-center min-tablet">Groupe</th>
                      <th class="text-center all">Vote</th>
                      <th class="text-center all">Loyauté</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($deputes as $v_depute): ?>
                      <tr>
                        <td class="name">
                          <a href="<?= base_url() ?>deputes/<?= $v_depute['dptSlug'] ?>/depute_<?= $v_depute['nameUrl'] ?>" target="_blank" class="no-decoration underline"><?= $v_depute['nameFirst'].' '.$v_depute['nameLast'] ?></a>
                        </td>
                        <td class="text-center"><?= $v_depute['libelle'] ?></td>
                        <td class="text-center vote sort-<?= $v_depute['vote_libelle'] ?>"><?= mb_strtoupper($v_depute['vote_libelle']) ?></td>
                        <td class="text-center sort-<?= $v_depute['loyaute_libelle'] ?>"><?= mb_strtoupper($v_depute['loyaute_libelle']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot>
                      <tr>
                          <th>Député</th>
                          <th>Groupes</th>
                          <th>Vote</th>
                          <th>Loyauté</th>
                      </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          <div class="bloc-savoir-plus d-md-none mt-5">
            <h3 class="subtitle">En savoir plus</h3>
            <div class="bloc-links">
              <div class="d-flex flex-column flex-lg-row">
                <?php if ($vote['dossierUrl']): ?>
                  <span class="d-flex justify-content-center align-items-center url_obf btn btn-secondary link my-1 my-lg-0 mx-lg-1" url_obf="<?= url_obfuscation($vote['dossierUrl']) ?>">
                    <span class="text">Le dossier</span>
                  </span>
                <?php endif; ?>
                <?php if ($vote['voteType'] == 'amendement' && !empty($documentLegislatif)): ?>
                  <span class="d-flex justify-content-center align-items-center url_obf btn btn-secondary link my-1 my-lg-0 mx-lg-1" url_obf="<?= url_obfuscation("https://www.assemblee-nationale.fr/dyn/" . $amdt['legislature'] . "/amendements/" . $documentLegislatif['numNotice'] ."/AN/".$amdt['numOrdre']) ?>">
                    <span class="text">L'amendement</span>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="bloc-social d-md-none mt-4">
            <h3 class="subtitle">Partagez ce vote</h3>
            <!-- DESIGN https://feralvoice.com/social-media-sharing-buttons/ -->
            <!-- Linkedin does not work -->
            <!-- Whatsapp à faire -->
            <div class="d-flex flex-row flex-wrap social-share-bloc">
              <button type="button" name="button" class="btn social-share twitter twitter-bg d-flex">
                <img src="<?= asset_url() ?>imgs/logos/twitter-no-round.png" alt="Partagez sur Twitter">
                <span>Twitter</span>
              </button>
              <button type="button" name="button" class="btn social-share facebook fcb-bg d-flex">
                <img src="<?= asset_url() ?>imgs/logos/facebook-no-round.png" alt="Partagez sur Twitter">
                <span>Facebook</span>
              </button>
              <button type="button" name="button" class="btn social-share linkedin linkedin-bg d-flex">
                <img src="<?= asset_url() ?>imgs/logos/linkedin-no-round.png" alt="Partagez sur Linkedin">
                <span>Linkedin</span>
              </button>
              <button type="button" name="button" class="mobileShow btn social-share whatsapp whatsapp-bg">
                <img src="<?= asset_url() ?>imgs/logos/whatsapp-no-round.png" alt="Partagez sur Whatsapp">
                <span>Whatsapp</span>
              </button>
            </div>
          </div>
          <div class="bloc-nos-lois d-md-none mt-4">
            <h3 class="subtitle">Prenez position</h3>
            <p>Vous aimez Datan ? Alors vous aimerez sûrement <b>Nos Lois</b>, une application permettant de donner son avis sur les textes en discussion à l'Assemblée.</p>
            <div class="d-flex justify-content-center">
              <div class="card align-items-center link nos-lois url_obf my-2 p-2" url_obf="<?= url_obfuscation("https://www.noslois.fr/") ?>" >
                <img src="<?= asset_url() ?>imgs/logos/nos-lois.png" alt="Logo Nos Lois">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid pg-vote-individual mt-5" id="container-always-fluid">
      <div class="row votes-next d-flex flex-md-row flex-column align-items-center justify-content-center py-4">
        <?php if ($vote_previous != FALSE): ?>
          <a class="btn" href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/vote_<?= $vote_previous ?>" role="button">
            <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?> Vote précédent</a>
        <?php endif; ?>
        <a class="btn" href="<?= base_url() ?>votes" role="button">Tous les votes</a>
        <?php if ($vote_next != FALSE): ?>
          <a class="btn" href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/vote_<?= $vote_next ?>" role="button">Vote suivant <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="container pg-vote-individual">
      <div class="row votes-decryptes mb-5">
        <div class="col-12 py-5">
          <h2>Les derniers votes décryptés par Datan</h2>
        </div>
        <div class="col-12 carousel-container bloc-carousel-votes-flickity">
          <?php $this->load->view('votes/partials/votes_carousel.php', array('votes' => $votes_datan)) ?>
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
    <script type="text/javascript">
    // Pie chart
    new Chart(document.getElementById("pie-chart"), {
        type: 'pie',
        data: {
          labels: ["Pour", "Abstention", "Contre"],
          datasets: [{
            label: "Population (millions)",
            backgroundColor: ["#00B794", "#FFBA49","#C5283D"],
            data: [<?= $vote['pour'] ?>, <?= $vote['abstention'] ?>, <?= $vote['contre'] ?>]
          }]
        },
        options: {
          title: {
            display: false
          },
          legend: {
            display: false
          },
          layout: {
              padding: {
                left: 0,
                right: 0,
                top: 0,
                bottom: 0
              }
          }
        }
    });

    </script>
