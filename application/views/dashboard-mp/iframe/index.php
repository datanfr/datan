<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <?php $this->load->view('dashboard-mp/partials/breadcrumb.php', $breadcrumb) ?>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <a class="btn btn-outline-secondary font-weight-bold" href="<?= base_url() ?>dashboard">
            <?= file_get_contents(asset_url() . "imgs/icons/arrow_left.svg") ?>
            Retour
          </a>
        </div>

        <div class="col-lg-7 my-5">
          <?php if ($this->session->flashdata('flash')) : ?>
            <div class="alert alert-primary font-weight-bold text-center mt-4" role="alert"><?= $this->session->flashdata('flash') ?></div>
          <?php endif; ?>
          <h1 class="font-weight-bold mb-0 text-dark"><?= $title ?></h1>
        </div>
      </div>

      <div class="row">
        <!-- Colonne gauche -->
        <div class="col-md-6">
          <!-- Texte de présentation qui prend toute la largeur -->
          <div class="card mb-4">
            <div class="card-body py-4">
              <h5 class="font-weight-bold text-primary">À quoi ça sert&nbsp;?</h5>
              <p>Ce générateur vous permet d'intégrer sur votre site une iframe contenant les informations de votre profil
                sur Datan. C’est une manière simple de valoriser votre travail parlementaire, directement
                sur votre site officiel.</p>
              <p>Vous pourrez ensuite choisir les types d’informations que vous souhaitez afficher (votes, explications, statistiques, etc.).</p>
            </div>
          </div>


          <div class="row">
            <div class="col-md-6">
              <!-- Partie gauche de la colonne de gauche (Checkbox + Options) -->
              <div class="mb-3">
                <label class="font-weight-bold">Catégories à afficher</label>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="all" id="cat-all">
                  <label class="form-check-label" for="cat-all">Toutes les catégories</label>
                </div>

                <div style="margin-left: 1rem;">
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="derniers-votes" id="cat1">
                    <label class="form-check-label" for="cat1">Mes derniers votes</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="positions-importantes" id="cat2">
                    <label class="form-check-label" for="cat2">Mes positions importantes</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="election" id="cat3">
                    <label class="form-check-label" for="cat3">Mon élection</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="explication" id="cat4">
                    <label class="form-check-label" for="cat3">Ma dernière explication</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="comportement-politique" id="cat5">
                    <label class="form-check-label" for="cat4">Mon comportement politique</label>

                    <!-- Sous-catégories -->
                    <div class="subcategory-wrapper ms-3">
                      <div class="form-check">
                        <input class="form-check-input subcategory-checkbox" type="checkbox" value="participation-votes" id="subcat-1">
                        <label class="form-check-label" for="subcat-1">Participation aux votes</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input subcategory-checkbox" type="checkbox" value="proximite-groupe" id="subcat-2">
                        <label class="form-check-label" for="subcat-2">Proximité au groupe</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input subcategory-checkbox" type="checkbox" value="proximite-groupes" id="subcat-3">
                        <label class="form-check-label" for="subcat-3">Proximité avec les groupes politiques</label>
                      </div>
                    </div>
                  </div>
                </div> <!-- fin du bloc indente -->
              </div>


              <div class="mb-3">
                <label class="font-weight-bold">Options d'affichage</label>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="hide-main-title" id="hideMainTitle">
                  <label class="form-check-label" for="hideMainTitle">Cacher le titre principal</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="hide-secondary-title" id="hideSecondaryTitle">
                  <label class="form-check-label" for="hideSecondaryTitle">Cacher le titre secondaire</label>
                </div>
                <!-- Option première et troisième personne-->
                <div class="form-check">
                  <label class="form-check-label" for="first-person">À la première personne</label>
                  <input type="radio" class="form-check-input" id="first-person" name="person-mode" value="first" checked>
                </div>
                <div class="form-check">
                  <label class="form-check-label" for="second-person">À la troisième personne</label>
                  <input type="radio" class="form-check-input" id="second-person" name="person-mode" value="second">
                </div>
              </div>
            </div>


            <!-- Partie droite de la colonne de gauche (Code à copier) -->
            <div class="col-md-6">
              <div>
                <label for="iframeCode" class="form-label font-weight-bold">Code à copier</label>
                <!-- Zone grisée -->
                <div class="bg-light mb-3" style="height: 200px; width: 100%;">
                  <textarea
                    class="form-control h-100 w-100"
                    id="iframeCode"
                    readonly
                    style="resize: none;">
                      </textarea>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button
                    class="btn btn-outline-secondary"
                    type="button"
                    id="copyIframeCode"
                    data-clipboard-target="#iframeCode">
                    Copier
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Colonne droite -->
        <div class="col-md-6 px-lg-5 px-md-3 py-md-5 py-lg-0 mt-md-0 mt-4">
          <h2 class="font-weight-bold text-black">Aperçu</h2>
          <div id="iframe-wrapper" data-slug="<?= $name_url ?>"></div>

          <?php if (empty($explanations)) : ?>
            <div class="alert alert-warning">
              Vous n'avez pas encore d'explication.
              <a href="<?= base_url() ?>dashboard/explications/liste" class="alert-link">
                Rédigez votre première explication
              </a>.
            </div>
          <?php elseif ($has_published === false) : ?>
            <div class="alert alert-warning">
              Vous n'avez pas encore d'explication publiée.
              <a href="<?= base_url() ?>dashboard/explications/liste" class="alert-link">
                Cliquez ici pour gérer vos explications
              </a>.
            </div>
          <?php endif; ?>

          <iframe id="iframePreview" src="" width="100%" height="600px" frameborder="1"></iframe>
        </div>

      </div>
    </div>
  </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  $(document).ready(function() {
    // const hasExplanation = " + JSON.stringify(!empty($has_explanation)) + ";
    const alertDiv = $('.alert-warning');
    const checkboxes = $('input[type="checkbox"]');
    const allCategoriesCheckbox = $('#cat-all');
    const mainCategoriesCount = $('.category-checkbox').length;
    const subcategoriesCount = $('.subcategory-checkbox').length;

    let iframeUrl = "";
    if (alertDiv.length) {
      alertDiv.hide();
    }

    function initializeIframeUrl() {
      const slugElement = $('#iframe-wrapper');
      if (slugElement.length) {
        const slug = slugElement.data('slug');
        return "http://dev-datan.fr/iframe/depute/" + slug;
      }
      return "";
    }

    function getSelectedCategories() {
      const categories = [];
      if (!allCategoriesCheckbox.prop('checked')) {
        $('.category-checkbox:checked').each(function() {
          categories.push($(this).val());
        });
      }
      return categories;
    }

    function getSelectedSubcategories() {
      const subcategories = [];
      $('.subcategory-checkbox:checked').each(function() {
        subcategories.push("comportement-politique." + $(this).val());
      });
      return subcategories;
    }

    function getSelectedOptions() {
      const options = [];
      if ($('#hideMainTitle').prop('checked')) {
        options.push('main-title=hide');
      }
      if ($('#hideSecondaryTitle').prop('checked')) {
        options.push('secondary-title=hide');
      }
      const personMode = $('input[name="person-mode"]:checked').val();
      console.log("valeur", personMode);
      if (personMode === 'first') {
        options.push('first-person=true');
      } else {
        options.push('person-mode=third');
      }

      return options;
    }

    function buildIframeUrl() {
      let iframeUrl = initializeIframeUrl();
      const categories = getSelectedCategories();
      const subcategories = getSelectedSubcategories();
      const options = getSelectedOptions();
      console.log(options)
      const politicalBehaviorCategory = $("#cat5");
      const params = [];
      let finalCategories = [];

      if (allCategoriesCheckbox.prop('checked')) {
        if (options.length > 0) {
          params.push(...options);
        }
        if (params.length > 0) {
          iframeUrl += "?" + params.join('&');
        }
        return iframeUrl;
      }

      if (categories.length === mainCategoriesCount) {
        allCategoriesCheckbox.prop('checked', true);
        toggleCategoryCheckboxes(true);
        toggleSubCategoryCheckboxes(true);
        if (options.length > 0) {
          params.push(...options);
          iframeUrl += "?" + params.join('&');
        }
        return iframeUrl;
      }

      finalCategories = [...categories];

      subcategories.forEach(function(subcategory) {
        if (politicalBehaviorCategory.prop('checked')) {
          return;
        }
        finalCategories.push(subcategory);
      });

      if (subcategories.length === subcategoriesCount) {
        finalCategories = finalCategories.filter(function(cat) {
          return !cat.startsWith("comportement-politique.");
        });
        politicalBehaviorCategory.prop('checked', true);
        toggleSubCategoryCheckboxes(true);
        if (!finalCategories.includes("comportement-politique")) {
          finalCategories.push("comportement-politique");
        }
      }

      if (finalCategories.length > 0) {
        params.push("categories=" + finalCategories.join(','));
      }
      if (options.length > 0) {
        params.push(...options);
      }
      if (params.length > 0) {
        iframeUrl += "?" + params.join('&');
      }

      return iframeUrl;
    }

    function updateIframeAndCode(iframeUrl) {
      const categories = getSelectedCategories();
      const subcategories = getSelectedSubcategories();
      const iframePreview = $('#iframePreview');
      const iframeCodeElement = $('#iframeCode');

      if (allCategoriesCheckbox.prop('checked') || categories.length > 0 || subcategories.length > 0) {
        iframePreview.attr('src', iframeUrl);
        const iframeCode = `<iframe src="${iframeUrl}" width="400" height="600" frameborder="0"></iframe>`;
        iframeCodeElement.val(iframeCode);
      } else {
        iframePreview.attr('src', '');
        iframeCodeElement.val("");
      }
    }

    function handlePreview() {
      const iframeUrl = buildIframeUrl();
      const shouldShowAlert = checkboxes.toArray().some(cb =>
        $(cb).prop('checked') && ($(cb).val().includes('all') || $(cb).val().includes('explication'))
      );
      if (alertDiv.length) {
        alertDiv.toggle(shouldShowAlert);
      }
      updateIframeAndCode(iframeUrl);
    }

    function toggleCategoryCheckboxes(disable) {
      $('.category-checkbox').each(function() {
        $(this).prop('checked', disable);
        $(this).prop('disabled', disable);
      });
    }

    function toggleSubCategoryCheckboxes(disable) {
      $('.subcategory-checkbox').each(function() {
        $(this).prop('checked', disable);
        $(this).prop('disabled', disable);
      });
    }

    function handlePoliticalBehaviorCategory() {
      const politicalBehaviorCategory = $('#cat5');
      politicalBehaviorCategory.on('change', function() {
        if ($(this).prop('checked')) {
          toggleSubCategoryCheckboxes(true);
        } else {
          toggleSubCategoryCheckboxes(false);
        }
      });
    }

    $('input[name="person-mode"]').on('change', function() {
      const iframeUrl = buildIframeUrl(); 
      updateIframeAndCode(iframeUrl); 
    });


    allCategoriesCheckbox.on('change', function() {
      if ($(this).prop('checked')) {
        toggleCategoryCheckboxes(true);
        toggleSubCategoryCheckboxes(true);
      } else {
        toggleCategoryCheckboxes(false);
        toggleSubCategoryCheckboxes(false);
      }
    });

    handlePoliticalBehaviorCategory();

    allCategoriesCheckbox.prop('checked', true);
    toggleCategoryCheckboxes(true);
    toggleSubCategoryCheckboxes(true);

    const shouldShowAlert = checkboxes.toArray().some(cb =>
      $(cb).prop('checked') && ($(cb).val().includes('all') || $(cb).val().includes('explication'))
    );
    if (alertDiv.length) {
      alertDiv.toggle(shouldShowAlert);
    }

    iframeUrl = initializeIframeUrl();
    updateIframeAndCode(iframeUrl);

    checkboxes.each(function() {
      $(this).on('change', handlePreview);
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
<script>
  new ClipboardJS('#copyIframeCode');
</script>