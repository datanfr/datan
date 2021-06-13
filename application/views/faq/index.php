<div class="container-fluid pg-faq py-5" style="background-color: #00b794; color: #fff">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1><?= $title ?></h1>
        <div class="form-group">
          <div class="input-group">
            <input class="form-control form-control-lg filled-input" placeholder="Cherchez ..." type="text">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container pg-faq my-5">
  <div class="row">
    <div class="col-xl-4">
      <div class="card card-categories">
        <h2 class="card-header">Categories</h2>
        <ul class="list-group">
          <li class="list-group-item d-flex align-items-center active rounded-0">
            Datan <span class="badge badge-light badge-pill ml-3">06</span>
          </li>
          <li class="list-group-item d-flex align-items-center">
            Nos statistiques <span class="badge badge-light badge-pill ml-3">14</span>
          </li>
          <li class="list-group-item d-flex align-items-center">
            L'Assemblée nationale <span class="badge badge-light badge-pill ml-3">14</span>
          </li>
          <li class="list-group-item d-flex align-items-center">
            Les députés <span class="badge badge-light badge-pill ml-3">14</span>
          </li>
          <li class="list-group-item d-flex align-items-center">
            Les groupes parlementaires <span class="badge badge-light badge-pill ml-3">14</span>
          </li>
          <li class="list-group-item d-flex align-items-center">
            Les votes <span class="badge badge-light badge-pill ml-3">14</span>
          </li>
        </ul>
      </div>
    </div> <!-- END CATEGORIES -->
    <div class="col-xl-8">
      <div class="card card-category">
        <h2 class="card-header">Datan</h2>
        <div class="accordion accordion-type-2" id="accordion_2">
          <div class="card card-question">
            <div class="card-header d-flex justify-content-between activestate">
              <a role="button" data-toggle="collapse" href="#collapse_1i" aria-expanded="true" class="no-decoration">
                <?= file_get_contents(base_url() . '/assets/imgs/icons/plus.svg') ?><span class="ml-3">Qui sommes nous ?</span>
              </a>
            </div>
            <div id="collapse_1i" class="collapse show" data-parent="#accordion_2" role="tabpanel">
              <div class="card-body">
                The Intellectual Property disclosure will inform users that the contents, logo and other visual media you created is your property and is protected by copyright laws.
              </div>
            </div>
          </div>
          <div class="card card-question">
            <div class="card-header d-flex justify-content-between">
              <a class="collapsed no-decoration" role="button" data-toggle="collapse" href="#collapse_2i" aria-expanded="false">
                <?= file_get_contents(base_url() . '/assets/imgs/icons/plus.svg') ?><span class="ml-3">Qui sommes nous ?</span>
              </a>
            </div>
            <div id="collapse_2i" class="collapse" data-parent="#accordion_2">
              <div class="card-body">
                A Termination clause will inform that users’ accounts on your website and mobile app or users’ access to your website and mobile (if users can’t have an account with you) can be terminated in case of abuses or at your sole discretion.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
