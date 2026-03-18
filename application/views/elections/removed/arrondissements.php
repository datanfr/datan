<?php 

      $data['isPLM'] = in_array($insee, ['75056', '13055', '69123']);

      if($data['isPLM']) {
        $data['arrondissements'] = $this->elections_model->get_municipales_listes($insee, TRUE);
        foreach ($data['arrondissements'] as $arr => $lists) {
          $data['arrondissements'][$arr] = $this->elections_model->get_nuances_edited($lists);
        }

        $data['arrondissements_view'] = array();
        foreach ($data['arrondissements'] as $arrLabel => $lists) {
          $data['arrondissements_view'][] = array(
            'label' => $arrLabel,
            'lists' => $lists,
          );
        }
        $data['arrondissements_first_label'] = !empty($data['arrondissements_view']) ? $data['arrondissements_view'][0]['label'] : NULL;
      }

?>


<h2 class="mt-5">Listes candidates aux municipales 2026 à <?= $ville['commune_nom'] ?></h2>
      <?php if($isPLM): ?>
        <ul class="nav nav-tabs mt-4" id="scrutinTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active px-3" id="municipal-tab" data-toggle="tab" href="#municipal" role="tab">
              Scrutin municipal
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-3" id="arrondissement-tab" data-toggle="tab" href="#arrondissement" role="tab">
              Arrondissements
            </a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="municipal" role="tabpanel">
            <?php //$this->view('elections/partials/_lists_accordion.php', array('arrondissements' => FALSE)) ?>
          </div>
          <div class="tab-pane fade" id="arrondissement" role="tabpanel">
            <p class="text-muted mt-4">À Paris, Lyon et Marseille, chaque électeur dispose de deux bulletins le jour du vote : un pour élire les conseillers de son arrondissement (ou secteur) et un autre pour élire les conseillers municipaux à l’échelle de toute la ville.</p>
            <?php if(!empty($arrondissements)): ?>
              <div class="dropdown mt-3">
                <button type="button" id="arrondissementDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span id="arrondissementDropdownLabel"><?= htmlspecialchars($arrondissements_first_label) ?></span>
                    <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                  </svg>
                </button>
                <div class="dropdown-menu shadow-sm" style="max-height: 300px; overflow-y: auto;" aria-labelledby="arrondissementDropdown">
                    <?php foreach($arrondissements_view as $arrondissement): ?>
                        <a class="dropdown-item arrondissement-select-item rounded" href="#"
                            data-arr="<?= htmlspecialchars($arrondissement['label']) ?>">
                            <?= $arrondissement['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
              <div id="arrondissementsContent" class="mt-4">
                <?php foreach($arrondissements_view as $arrondissement): ?>
                  <div class="arrondissement-block" data-arr="<?= htmlspecialchars($arrondissement['label']) ?>" <?= $arrondissement['label'] !== $arrondissements_first_label ? 'style="display:none"' : '' ?> >
                    <?php
                      //$this->view('elections/partials/_lists_accordion.php', array('listes' => $arrondissement['lists'], 'arrondissements' => TRUE));
                    ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="mt-3">Aucun arrondissement disponible.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <?php //$this->view('elections/partials/_lists_accordion.php', array('arrondissements' => FALSE)) ?>
      <?php endif; ?>