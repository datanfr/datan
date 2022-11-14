  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <?php $this->load->view('dashboard-mp/partials/breadcrumb.php', $breadcrumb) ?>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-lg-6">
            <h1 class="m-0 text-dark font-weight-bold">Espace personnel - <?= $depute['nameFirst'] ?> <?= $depute['nameLast'] ?></h1>
            <p class="mt-3">Bienvenue sur votre espace personnel sur Datan. Cet espace vous permet d'avoir accès à des fonctionnalités dédiés aux parlementaires. Si vous avez des questions, n'hésitez pas à nous contacter : <i>info@datan.fr</i></p>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="mt-0 font-weight-bold">Vos explications de vote en brouillon</h5>
              </div>
              <div class="card-body">
                <?php if ($votes_explained): ?>
                  <p>Vous avez une ou plusieurs explications de vote en brouillon. N'hésitez pas à les terminer pour les publier sur votre page Datan.</p>
                  <table class="table mt-4">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col">Scrutin</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votes_explained as $key => $value): ?>
                        <tr>
                          <td class="font-weight-bold"><?= $value['vote_titre'] ?></td>
                          <td class="text-center"><span class="badge badge<?= ucfirst($value['vote_depute']) ?>" style="font-size: 16px"><?= ucfirst($value['vote_depute']) ?></span></td>
                          <td>
                            <a class="btn btn-primary d-flex align-items-center justify-content-center font-weight-bold" href="<?= base_url() ?>dashboard-mp/explications/modify/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>">
                              <?= file_get_contents(asset_url()."imgs/icons/pencil-square.svg") ?>
                              <span class="ml-3">Modifier</span>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php else: ?>
                  <p>Vous n'avez pas d'explication de vote en brouillon. N'hésitez pas à en créer une nouvelle.</p>
                <?php endif; ?>
              </div>
              <div class="card-footer d-flex justify-content-center align-items-center">
                <a class="btn btn-primary font-weight-bold" style="font-size: 1.1rem" href="<?= base_url() ?>dashboard-mp/explications" role="button">Voir toutes vos explications de vote</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
