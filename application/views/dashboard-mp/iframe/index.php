<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <?php $this->load->view('dashboard-mp/partials/breadcrumb.php', $breadcrumb ?? []) ?>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <a class="btn btn-outline-secondary font-weight-bold mb-3" href="<?= base_url() ?>dashboard">
            <?= file_get_contents(asset_url() . "imgs/icons/arrow_left.svg") ?>
            Retour
          </a>
        </div>
        <div class="col-lg-8">
          <h1 class="font-weight-bold text-dark mb-4">Générer un iframe</h1>
          <div class="card mb-4">
            <div class="card-body py-4">
              <h5 class="font-weight-bold text-primary">À quoi ça sert&nbsp;?</h5>
              <p>Ce générateur vous permet d'intégrer sur votre site une iframe contenant les informations de votre profil 
                sur Datan. C’est une manière simple de valoriser votre travail parlementaire, directement 
                sur votre site officiel.</p>
              <p>Vous pourrez ensuite choisir les types d’informations que vous souhaitez afficher (votes, explications, statistiques, etc.).</p>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label class="font-weight-bold">Catégories à afficher :</label>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="derniers-votes" id="cat1">
          <label class="form-check-label" for="cat1">Mes derniers votes</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="explications" id="cat2">
          <label class="form-check-label" for="cat2">Mes positions importantes</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="election" id="cat3">
          <label class="form-check-label" for="cat3">Mon élection</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="comportement" id="cat4">
          <label class="form-check-label" for="cat4">Mon comportement politique</label>
        </div>
      </div>
      <div class="mb-3">
        <label class="font-weight-bold">Options d'affichage :</label>
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
    <div class="mt-3">
      <iframe id="iframePreview" src="" width="600px" height="600px" frameborder="1"></iframe>
    </div>
    <div class="mb-3">
    <label for="iframeCode" class="form-label font-weight-bold">Code à copier :</label>
    <div class="input-group">
      <input type="text" class="form-control" id="iframeCode" readonly value="">
      <button class="btn btn-outline-secondary" type="button" id="copyIframeCode" data-clipboard-target="#iframeCode">Copier</button>
  </div>
</div>
  </div>
</div>

<script> 
const previewButton = document.getElementById('previewButton');
console.log(previewButton);
previewButton.addEventListener('click', function() {

    const categories = [];
    if (document.getElementById('cat1').checked) {
        categories.push('derniers-votes');
    }
    if (document.getElementById('cat2').checked) {
        categories.push('positions-importantes');
    }
    if (document.getElementById('cat3').checked) {
        categories.push('election');
    }
    if (document.getElementById('cat4').checked) {
        categories.push('comportement-politique');
    }


    const options = [];
    if (document.getElementById('hideMainTitle').checked) {
        options.push('hide-main-title');
    }
    if (document.getElementById('hideSecondaryTitle').checked) {
        options.push('hide-secondary-title');
    }

    let iframeUrl = "http://dev-datan.fr/iframe/depute/marie-mesmeur";
   
   

  const titleOptions = [];
  if (document.getElementById('hideMainTitle').checked) {
      titleOptions.push('main-title=hide');
  }
  if (document.getElementById('hideSecondaryTitle').checked) {
      titleOptions.push('secondary-title=hide');
  }


  if (categories.length > 0 || titleOptions.length > 0) {
    iframeUrl += "?";
    if (categories.length > 0) {
      iframeUrl += "categories=" + categories.join(',');
    }
    if (titleOptions.length > 0) {
      if (categories.length > 0) iframeUrl += "&"; 
      iframeUrl += titleOptions.join('&');
    }
  }

      console.log(iframeUrl);


    document.getElementById('iframePreview').src = iframeUrl;

    
    const iframeCode = `<iframe src="${iframeUrl}" width="400" height="600" frameborder="0"></iframe>`;
    document.getElementById('iframeCode').value = iframeCode;
});

</script>

<script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
<script>
  new ClipboardJS('#copyIframeCode');
</script>

