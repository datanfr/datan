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
                <div class="form-check">
                  <input class="form-check-input category-checkbox" type="checkbox" value="derniers-votes" id="cat1">
                  <label class="form-check-label" for="cat1">Mes derniers votes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input category-checkbox" type="checkbox" value="explications" id="cat2">
                  <label class="form-check-label" for="cat2">Mes positions importantes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input category-checkbox" type="checkbox" value="election" id="cat3">
                  <label class="form-check-label" for="cat3">Mon élection</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input category-checkbox" type="checkbox" value="comportement" id="cat4">
                  <label class="form-check-label" for="cat4">Mon comportement politique</label>

                  <!-- Sous-catégories -->
                  <div class="subcategory-wrapper">
                    <div class="form-check">
                      <input class="form-check-input subcategory-checkbox" type="checkbox" value="sub1" id="subcat-1">
                      <label class="form-check-label" for="subcat-1">Participation aux votes</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input subcategory-checkbox" type="checkbox" value="sub2" id="subcat-2">
                      <label class="form-check-label" for="subcat-2">Proximité au groupe</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input subcategory-checkbox" type="checkbox" value="sub3" id="subcat-3">
                      <label class="form-check-label" for="subcat-3">Proximité avec les groupes politiques</label>
                    </div>
                  </div>
                </div>

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
              </div>

              <div class="mb-3">
                <button class="btn btn-primary font-weight-bold" id="previewButton">Générer l'iframe</button>
              </div>
            </div>


            <!-- Partie droite de la colonne de gauche (Code à copier) -->
            <div class="col-md-6">
              <div class="mt-3">
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
          <iframe id="iframePreview" src="" width="100%" height="600px" frameborder="1"></iframe>
        </div>
      </div>
    </div>
  </div>
</div>
</div>




<script>
  const previewButton = document.getElementById('previewButton');

  let iframeUrl = "";
  const allCategoriesChecked = document.getElementById('cat-all').checked;
  console.log(allCategoriesChecked);


  function initializeIframeUrl() {
    const slugElement = document.getElementById("iframe-wrapper");
    if (slugElement) {
      const slug = slugElement.dataset.slug;
      return "http://dev-datan.fr/iframe/depute/" + slug;
    }
    return "";
  }

  function getSelectedCategories() {
    const allCategoriesChecked = document.getElementById('cat-all').checked;
    const categoryMapping = {
      'cat1': 'derniers-votes',
      'cat2': 'positions-importantes',
      'cat3': 'election',
      'cat4': 'comportement-politique'
    };

    const categories = [];

    if (!allCategoriesChecked) {
      for (const id in categoryMapping) {
        if (document.getElementById(id).checked) {
          categories.push(categoryMapping[id]);
        }
      }
    }

    return categories;
  }

  function getSelectedSubcategories() {
    const subcategories = [];

    const selectedSubcategories = [];
    const subcategoryCheckboxes = document.querySelectorAll('.subcategory-checkbox');
    subcategoryCheckboxes.forEach(checkbox => {
      if (checkbox.checked) {
        selectedSubcategories.push(checkbox.value);
      }
    });

    if (selectedSubcategories.length > 0) {
      subcategories.push({
        category: 'comportement-politique',
        values: selectedSubcategories
      });
    }


    return subcategories;
  }


  function validateCategories(categories) {
    const allCategoriesChecked = document.getElementById('cat-all').checked;
    if (categories.length === 0 && !allCategoriesChecked) {
      alert("Veuillez sélectionner au moins une catégorie pour générer l'aperçu.");
      return false;
    }
    return true;
  }


  function getSelectedOptions() {
    const options = [];
    if (document.getElementById('hideMainTitle').checked) {
      options.push('main-title=hide');
    }
    if (document.getElementById('hideSecondaryTitle').checked) {
      options.push('secondary-title=hide');
    }
    return options;
  }

  function buildIframeUrl(categories, options, subcategories) {
    let iframeUrl = initializeIframeUrl();
    const params = [];

    const hasCat4 = categories.includes('comportement-politique');
    const hasSubcategories = subcategories.length > 0;


    if (!hasCat4 && hasSubcategories) {
      categories.push('comportement-politique');
    }


    const uniqueCategories = [...new Set(categories)];
    if (uniqueCategories.length > 0) {
      params.push("categories=" + uniqueCategories.join(','));
    }


    if (!hasCat4 && hasSubcategories) {
      subcategories.forEach(subcat => {
        const value = subcat.values.join(',');
        params.push(`${subcat.category}-subcategory=${value}`);
      });
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
    document.getElementById('iframePreview').src = iframeUrl;
    const iframeCode = `<iframe src="${iframeUrl}" width="400" height="600" frameborder="0"></iframe>`;
    document.getElementById('iframeCode').value = iframeCode;
  }



  function handlePreviewButtonClick() {
    const categories = getSelectedCategories();
    const subcategories = getSelectedSubcategories();

    // if (!validateCategories(categories)) {
    //   return;
    // }   ---------------> à revoir pour faire en sorte de prendre en compte les sous catégories également

    const options = getSelectedOptions();
    const finalIframeUrl = buildIframeUrl(categories, options, subcategories);
    console.log(finalIframeUrl);

    updateIframeAndCode(finalIframeUrl);
  }


  initializeIframeUrl();

  previewButton.addEventListener('click', handlePreviewButtonClick);



  document.addEventListener('DOMContentLoaded', function() {
    const allCheckbox = document.getElementById('cat-all');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const politicalBehaviorCheckbox = document.getElementById('cat4');

    const subCategoryCheckboxes = document.querySelectorAll('.subcategory-checkbox');



    function toggleCategoryCheckboxes(disable) {
      categoryCheckboxes.forEach(cb => {
        cb.checked = disable;
        cb.disabled = disable;
      });
    }

    function toggleSubCategoryCheckboxes(disable) {
      subCategoryCheckboxes.forEach(cb => {
        cb.checked = disable;
        cb.disabled = disable;
      });
    }



    allCheckbox.addEventListener('change', function() {
      toggleCategoryCheckboxes(this.checked);
      // toggleSubCategoryCheckboxes(this.checked);  -----> à revoir
    });

    politicalBehaviorCheckbox.addEventListener('change', function() {
      toggleSubCategoryCheckboxes(this.checked);
    });



    categoryCheckboxes.forEach(cb => {
      cb.addEventListener('change', function() {
        if (allCheckbox.checked) {
          allCheckbox.checked = false;
        }
      });
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
<script>
  new ClipboardJS('#copyIframeCode');
</script>