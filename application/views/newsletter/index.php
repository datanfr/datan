<div class="container-fluid pg-newsletter" id="container-always-fluid">
  <div class="row">
    <div class="col-lg-6 d-none d-lg-block m-0 p-0 login_img" style="min-height: 800px">
      <div class="img-container">
      </div>
    </div>
    <div class="col-lg-6">
      <?= form_open('newsletter', array('id'=> 'newsletterPage')); ?>
      <div class="row my-5">
        <div class="col-lg-8 col-md-10 col-10 offset-lg-2 offset-md-1 offset-1">
          <div class="row my-lg-5 d-flex flex-column justify-content-center login_form">
            <h1><?= $title ?></h1>
            <div class="mt-3 mb-5">
              <p>Vous souhaitez recevoir des informations du projet <b>Datan</b> ? Découvrir les <b>derniers votes</b> à l'Assemblée nationale ? En savoir plus sur <b>l'activité de votre député</b> ?</p>
              <p>N'attendez plus, abonnez-vous à notre newsletter !</p>
            </div>
            <?php if ($this->session->flashdata('login_failed')): ?>
              <div class="alert alert-danger mb-4 text-center" role="alert">
                <?= ($this->session->flashdata('login_failed')) ?>
              </div>
            <?php endif; ?>
            <span class="mb-2 legend">Adresse e-mail</span>
            <div class="form-group">
              <input type="email" name="email" class="form-control" placeholder="Exemple : benoit@assemblee-nationale.fr" required autofocus>
            </div>
            <div id="newsletterSuccess" class="d-none alert alert-success mt-3" role="alert">
              Félicitations. Vous serez maintenant informés des actualités politiques de nos députés et un peu du site aussi :)
            </div>
            <div id="newsletterFailed" class="d-none alert alert-danger mt-3" role="alert">
              Quelque chose s'est mal passé. Vous êtes sans doute déjà inscrit !
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-3">S'abonner</button>
          </div>
        </div>
        <?= form_close(); ?>
      </div>
    </div>
  </div>
</div>
