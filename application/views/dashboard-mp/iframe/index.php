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
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-body py-4">
              <h5 class="font-weight-bold text-primary">À quoi ça sert&nbsp;?</h5>
              <p>
                Ce générateur vous permet d’intégrer sur votre site un iframe affichant les données issues de votre profil Datan.
                C’est un moyen simple et efficace de valoriser votre activité parlementaire
                directement sur votre site officiel.
              </p>
              <p>
                Vous pouvez personnaliser son contenu en choisissant les catégories à afficher ainsi que les options de présentation.
              </p>

            </div>
          </div>


          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="font-weight-bold">Catégories à afficher</label>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="all" id="cat-all">
                  <label class="form-check-label" for="cat-all">Toutes les catégories</label>
                </div>

                <div style="margin-left: 1rem;">
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="positions-importantes" id="cat2">
                    <label class="form-check-label" for="cat2">Mes positions importantes</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="derniers-votes" id="cat1">
                    <label class="form-check-label" for="cat1">Mes derniers votes</label>
                  </div>

                  <!-- Partial à terminer, erreur sur les candidats élus au premier tour -->
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="election" id="cat3">
                    <label class="form-check-label" for="cat3">Mon élection</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="explication" id="cat4">
                    <label class="form-check-label" for="cat4">Ma dernière explication</label>
                  </div>
                    <!-- Partial à terminer -->
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="questions" id="cat5">
                    <label class="form-check-label" for="cat5">Mes dernières questions</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="comportement-politique" id="cat6">
                    <label class="form-check-label" for="cat6">Mon comportement politique</label>

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
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" id="first-person" name="person-mode" value="first" checked>
                  <label class="form-check-label" for="first-person">À la première personne</label>
                </div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" id="second-person" name="person-mode" value="second">
                  <label class="form-check-label" for="second-person">À la troisième personne</label>
                </div>

              </div>
            </div>

            <div class="col-md-6">
              <div>
                <label for="iframeCode" class="form-label font-weight-bold">Code à copier</label>
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
              <a href="<?= base_url() ?>dashboard/explications/" class="alert-link">
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


</script>