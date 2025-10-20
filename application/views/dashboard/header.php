<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="robots" content="noindex,nofollow">

  <title><?= $title_meta ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= asset_url() ?>imgs/favicon/datan_favicon.ico" />
  <link rel="shortcut icon" href="#" />
  <!-- Apple icons -->
  <link rel="apple-touch-icon" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon.png" />
  <link rel="apple-touch-icon" sizes="57x57" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-57x57.png" />
  <link rel="apple-touch-icon" sizes="72x72" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-72x72.png" />
  <link rel="apple-touch-icon" sizes="76x76" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-76x76.png" />
  <link rel="apple-touch-icon" sizes="114x114" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-114x114.png" />
  <link rel="apple-touch-icon" sizes="120x120" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-120x120.png" />
  <link rel="apple-touch-icon" sizes="144x144" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-144x144.png" />
  <link rel="apple-touch-icon" sizes="152x152" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-152x152.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="<?= asset_url() ?>imgs/favicon/apple-touch-icon-180x180.png" />

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
  <link rel="stylesheet" href="<?= asset_url() ?>css/dashboard/ckeditor5.css">
  <!-- ChartJS -->
  <script src="<?= asset_url() ?>js/libraries/chart.js/chart.min.js"></script>
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
          <a href="<?= base_url() ?>" class="nav-link font-weight-bold text-primary">Datan</a>
        </li>
        <?php if ($type == 'mp'): ?>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url() ?>dashboard" class="nav-link">Dashboard</a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>" class="nav-link" target="_blank">Page député<?= $depute['gender']['e'] ?></a>
          </li>
        <?php endif; ?>
        <?php if ($this->password_model->is_admin()): ?>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url() ?>scripts" class="nav-link">Scripts</a>
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
            <a href="<?= base_url() ?>cache/delete_all" class="nav-link">Delete cache</a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url() ?>admin/logs" class="nav-link">CI Logs</a>
          </li>
        <?php endif; ?>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="<?= base_url() ?>mon-compte" class="nav-link">Mon compte</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="<?= base_url(); ?>logout" class="nav-link text-danger">
            <?= file_get_contents(base_url() . '/assets/imgs/icons/bi-box-arrow-right.svg') ?>
            <span class="ml-1">Déconnexion</span>
          </a>
        </li>
      </ul>

    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo removed -->

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex justify-content-center">
          <?php if ($type == 'mp'): ?>
            <a href="<?= base_url() ?>dashboard">
              <div class="info d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-center">
                  <div class="depute-img-circle d-flex justify-content-center">
                    <picture>
                      <source srcset="<?= asset_url() ?>imgs/deputes_nobg_webp/depute_<?= $depute['idImage'] ?>_webp.webp" type="image/webp">
                      <source srcset="<?= asset_url() ?>imgs/deputes_nobg/depute_<?= $depute['idImage'] ?>.png" type="image/png">
                      <img src="<?= asset_url() ?>imgs/deputes_original/depute_<?= $depute['idImage'] ?>.png" width="150" height="192" alt="Hendrik Davi">
                    </picture>
                  </div>
                </div>
                <span class="d-block text-center mt-2 text-white font-weight-bold"><?= $depute['nameFirst'] . ' ' . $depute['nameLast'] ?></span>
              </div>
            </a>
          <?php endif; ?>
          <?php if ($type == 'team'): ?>
            <div class="info">
              <a href="<?= base_url() ?>admin" class="d-block"><?= $username ?></a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <?php if ($type == 'team'): ?>
              <!-- Votes datan -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <p>
                    Votes décryptés
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
              <!-- TABLEAUX SOCIAL MEDIA -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <p>
                    Tableaux analyse
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
                    <a href="<?= base_url() ?>admin/socialmedia/x" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Comptes X députés</p>
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
                    <a href="<?= base_url() ?>admin/elections/legislatives-2024" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Législatives 2024</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>admin/elections/europeennes-2024" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Européennes 2024</p>
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
              <li class="nav-item">
                <a href="<?= base_url() ?>admin/exposes" class="nav-link">
                  <p>
                    Exposés des motifs
                  </p>
                </a>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <p>
                    Campagnes de dons
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?= base_url() ?>admin/campagnes" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Tous les campagnes</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>admin/campagnes/create" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Créer une campagne</p>
                    </a>
                  </li>
                </ul>
              </li>
            <?php endif; ?>
            <?php if ($type == 'mp'): ?>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>dashboard/explications">Explications de vote</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>dashboard/explications/liste">Nouvelle explication</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>dashboard/iframe">Générer un iframe</a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </aside>