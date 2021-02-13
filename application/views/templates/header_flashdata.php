<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title_meta ?></title>
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="<?= asset_url() ?>css/main.css">
    <link rel="stylesheet" type="text/css" href="<?= asset_url() ?>css/fonts/font_open_sans.css" /> <!-- font with 400,600,800 -->
    <link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css">
    <link rel="stylesheet" type="text/css" href="<?= asset_url() ?>css/circle.css">
    <link rel="stylesheet" type="text/css" href="<?= asset_url() ?>css/Chart.min.css">
    <!--<link rel="stylesheet" type="text/css" href="<?= asset_url() ?>css/jquery.dataTables.min.css">-->
    <!--<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.20/datatables.min.css"/>-->
    <link rel="stylesheet" type="text/css" href="<?= asset_url() ?>css/dataTables.bootstrap4.min.css"/>

    <!-- JS -->
    <script src="<?= asset_url(); ?>js/Chart.min.js"></script>
    <script src="https://rawgit.com/chartjs/chartjs-plugin-annotation/master/chartjs-plugin-annotation.js"></script> <!-- can be deleted? -->
    <script src="<?= asset_url(); ?>js/jquery-3.3.1.slim.min.js"></script>
    <style media="screen">
      body {
        position: relative;

      }
    </style>
  </head>
  <body data-spy="scroll" data-target="#navScrollspy" data-offset="90">
  <header>
    <section>
      <nav class="navbar navbar-expand-lg navbar-dark" id="navbar-datan">
        <a class="navbar-brand mx-auto p-0" href="<?= base_url(); ?>" style="text-align: center">
          <img src="<?= asset_url(); ?>imgs/datan/logo_white_transp_beta.png" alt="">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar_datan" aria-controls="navbar_datan" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-1 dual-collapse2" id="navbar_datan">
          <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url(); ?>deputes">Députés</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>votes">Votes</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>groupes">Groupes</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url(); ?>classements">Classements</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url(); ?>posts">Blog</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url(); ?>a-propos">A propos</a>
            </li>

            <?php if (($this->session->userdata('type') == 'admin') || ($this->session->userdata('type') == 'writer')): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Admin
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <?php if (($this->session->userdata('type') == 'admin') || ($this->session->userdata('type') == 'writer')): ?>
                    <a class="dropdown-item" href="<?= base_url(); ?>admin/">Dashboard</a>
                  <?php endif; ?>
                  <?php if (($this->session->userdata('type') == 'admin')): ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= base_url(); ?>scripts/">Scripts</a>
                  <?php endif; ?>
                </div>
              </li>
              <?php endif; ?>

            <?php if (!$this->session->userdata('logged_in')): ?>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url(); ?>users/register">Inscription</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url(); ?>login">Connexion</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
        <div class="navbar-collapse collapse order-2 dual-collapse2">
            <ul class="navbar-nav ml-auto">
              <?php if ($this->session->userdata('logged_in')): ?>
                <li class="nav-item">
                  <a href="<?= base_url(); ?>users/logout" class="nav-link">Déconnexion</a>
                </li>
              <?php endif; ?>
            </ul>
        </div>
      </nav>
    </section>

    </header>
    <main>
        <!-- Flash message --> 
        <?php if ($this->session->flashdata('user_registered')): ?>
          <?= '<p class="alert alert-success">'.$this->session->flashdata('user_registered').'</p>'; ?>
        <?php endif; ?>

        <?php if ($this->session->flashdata('post_created')): ?>
          <?= '<p class="alert alert-success">'.$this->session->flashdata('post_created').'</p>'; ?>
        <?php endif; ?>

        <?php if ($this->session->flashdata('post_updated')): ?>
          <?= '<p class="alert alert-success">'.$this->session->flashdata('post_updated').'</p>'; ?>
        <?php endif; ?>

        <?php if ($this->session->flashdata('category_created')): ?>
          <?= '<p class="alert alert-success">'.$this->session->flashdata('category_created').'</p>'; ?>
        <?php endif; ?>

        <?php if ($this->session->flashdata('post_deleted')): ?>
          <?= '<p class="alert alert-success">'.$this->session->flashdata('post_deleted').'</p>'; ?>
        <?php endif; ?>

        <?php if ($this->session->flashdata('user_loggedin')): ?>
          <?= '<p class="alert alert-success">'.$this->session->flashdata('user_loggedin').'</p>'; ?>
        <?php endif; ?>

        <?php if ($this->session->flashdata('login_failed')): ?>
          <?= '<p class="alert alert-danger">'.$this->session->flashdata('login_failed').'</p>'; ?>
        <?php endif; ?>

        <?php if ($this->session->flashdata('user_loggedout')): ?>
          <?= '<p class="alert alert-danger">'.$this->session->flashdata('user_loggedout').'</p>'; ?>
        <?php endif; ?>
