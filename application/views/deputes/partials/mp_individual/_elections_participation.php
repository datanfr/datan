<!-- BLOC ELECTIONS -->
<?php if ($elections): ?>
  <div class="bloc-elections-history mt-5">
    <h2 class="mb-4 title-center">Ses participations électorales</h2>
    <p>
      <?= $title ?> a été candidat<?= $gender['e'] ?> <?= count($elections) > 1 ? 'à plusieurs élections' : 'à une élection' ?> alors qu'<?= $gender['pronom'] ?> était député<?= $gender['e'] ?>.
    </p>
    <table class="table">
      <tbody>
        <?php foreach ($elections as $election): ?>
          <tr>
            <td class="font-weight-bold"><?= $election['dateYear'] ?></td>
            <td><?= $election['libelle'] ?></td>
            <td><?= $election['district']['libelle'] ?></td>
            <td class="font-weight-bold sort-<?= $election['electedColor'] ?>"><?= $election['electedLibelle'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>