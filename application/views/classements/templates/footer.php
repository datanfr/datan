    <div class="col-lg-3">
      <div class="card card-right-posts sticky-top sticky-offset sticky-top-lg mt-5 mt-lg-0">
        <div class="card-header">
          Nos autres statistiques
        </div>
        <ul class="list-group list-group-flush">
          <?php if ($page != "deputes-age"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/deputes-age" class="no-decoration underline">L'âge des députés</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "groupes-age"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/groupes-age" class="no-decoration underline">L'âge moyen au sein des groupes</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "groupes-feminisation"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/groupes-feminisation" class="no-decoration underline">Le taux de féminisation des groupes</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "deputes-loyaute"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/deputes-loyaute" class="no-decoration underline">La proximité au groupe</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "groupes-cohesion"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/groupes-cohesion" class="no-decoration underline">La cohésion des groupes</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "deputes-participation"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/deputes-participation" class="no-decoration underline">La participation des députés</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "groupes-participation"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/groupes-participation" class="no-decoration underline">La participation des groupes</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "deputes-origine-sociale"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/deputes-origine-sociale" class="no-decoration underline">L'origine sociale des députés</a>
            </li>
          <?php endif; ?>
          <?php if ($page != "groupes-origine-sociale"): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>statistiques/groupes-origine-sociale" class="no-decoration underline">La représentativité des groupes</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
