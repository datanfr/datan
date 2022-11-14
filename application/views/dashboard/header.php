<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="robots" content="noindex,nofollow">

  <title><?= $title_meta ?></title>

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= asset_url() ?>css/dashboard/adminlte.css">
  <link rel="stylesheet" href="<?= asset_url() ?>css/dashboard/style.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <!-- DataTables -->
  <link rel="stylesheet" href="<?= asset_url() ?>css/dashboard/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="<?= asset_url() ?>css/dashboard/fixedHeader.dataTables.min.css">
  <link rel="stylesheet" href="<?= asset_url() ?>css/dashboard/buttons.dataTables.min.css">
  <!-- CKeditor -->
  <script src="https://cdn.ckeditor.com/ckeditor5/26.0.0/classic/ckeditor.js"></script>
  <!-- ChartJS -->
  <script src="<?= asset_url() ?>js/chart.min.js"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="<?= base_url () ?>" class="nav-link font-weight-bold text-primary">Datan</a>
        </li>
        <?php if ($this->password_model->is_mp()): ?>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>" class="nav-link" target="_blank">Page député</a>
          </li>
        <?php endif; ?>
        <?php if ($this->password_model->is_admin()): ?>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url () ?>scripts" class="nav-link">Scripts</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Logs scripts
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="<?= base_url() ?>admin/logs-scripts/daily">Daily</a>
              <a class="dropdown-item" href="<?= base_url() ?>admin/logs-scripts/weekly">Weekly</a>
              <a class="dropdown-item" href="<?= base_url() ?>admin/logs-scripts/get_files">Get files</a>
              <a class="dropdown-item" href="<?= base_url() ?>admin/logs-scripts/newsletter_votes">Newsletter votes</a>
            </div>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url () ?>cache/delete_all" class="nav-link">Delete cache</a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url () ?>admin/logs" class="nav-link">CI Logs</a>
          </li>
        <?php endif; ?>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="<?= base_url () ?>mon-compte" class="nav-link">Mon compte</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="<?= base_url(); ?>logout" class="nav-link">Déconnexion</a>
        </li>
      </ul>

      <!-- SEARCH FORM
      <form class="form-inline ml-3">
        <div class="input-group input-group-sm">
          <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-navbar" type="submit">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </form>
      -->

    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo removed -->

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <!--<img src="#" class="img-circle elevation-2" alt="User Image">-->
          </div>
          <div class="info">
            <?php if ($this->password_model->is_team()): ?>
              <a href="<?= base_url() ?>admin" class="d-block"><?= $username ?></a>
            <?php endif; ?>
            <?php if ($this->password_model->is_mp()): ?>
              <a href="<?= base_url() ?>dashboard-mp" class="d-block"><?= $username ?></a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <?php if ($this->password_model->is_team()): ?>
            <!-- Votes datan -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Votes_datan
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/votes" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Tous les votes</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/votes/create" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Créer un vote</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Votes_AN -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Votes_AN
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/votes_an/position" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Votes_AN (positions)</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/votes_an/cohesion" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Votes_AN (cohesion)</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- TABLEAUX ANALYSE -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Tableaux analyse
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/votes_an/majo_lost" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Majo perd</p>
                  </a>
                </li>
                <?php foreach((array) $groupes as $groupe) : ?>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/analyses/class_loyaute_group?group=<?= $groupe['libelleAbrev']?>" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Class. loyaute <?= $groupe['libelleAbrev'] ?></p>
                  </a>
                </li>
                <?php endforeach ?>
              </ul>
            </li>
            <!-- TABLEAUX SOCIAL MEDIA -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Tableaux social_media
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/socialmedia/deputes_entrants" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Députés entrants</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/socialmedia/deputes_sortants" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Députés sortants</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/socialmedia/postes_assemblee" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Postes Assemblée</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/socialmedia/groupes_entrants" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Groupe nouveau membre</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/socialmedia/historique" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Historique députés</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/socialmedia/twitter" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Comptes Twitter députés</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Blog posts -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Blog posts
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>blog" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Tous les posts</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>posts/create" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Créer un post</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- FAQ -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  FAQ
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/faq" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Tous les articles</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/faq/create" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Créer un article</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Parrainages -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Parrainages 2022
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/parrainages" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Liste</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Quizz -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Quizz
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/quizz" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Toutes les questions</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/quizz/create" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Créer une question</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Election -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <p>
                  Elections
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/elections/modifications-mps" class="nav-link bg-danger">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Modifs par députés</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/elections/legislatives-2022" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Législatives 2022</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/elections/presidentielle-2022" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Présidentielle 2022</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url() ?>admin/elections/regionales-2021" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Régionales 2021</p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Exposé des motifs -->
            <li class="nav-item">
              <a href="<?= base_url() ?>admin/exposes" class="nav-link">
                <p>
                  Exposés des motifs
                </p>
              </a>
            </li>
          <?php endif; ?>
          <?php if ($this->password_model->is_mp()): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dashboard-mp/explications">Explications de vote</a>
            </li>
          <?php endif; ?>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>
