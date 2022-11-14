<div class="card card-danger card-outline">
  <div class="card-header">
    <h5 class="m-0">Candidature aux élections législatives 2022</h5>
  </div>
  <div class="card-body">
    <p>Pour les élections législatives de 2022, le statut de votre candidature est</p>
    <?php if ($candidate): ?>
      <table class="table table-bordered">
        <tbody>
          <tr>
            <td colspan="2" class="text-center font-weight-bold"><?= $candidate['candidature'] == 1 ? 'Candidat' : 'Non candidat' ?><?= $depute['gender']['e'] ?></td>
          </tr>
          <tr>
            <th scope="row">Département de candidature</th>
            <td><?= $candidate['district']['libelle'] ? $candidate['district']['libelle'] : 'Non renseigné' ?></td>
          </tr>
          <tr>
            <th scope="row">Lien vers site de campagne</th>
            <td>
              <?php if ($candidate['link']): ?>
                <a href="<?= $candidate['link'] ?>" target="_blank"><?= $candidate['link'] ?></a>
                <?php else: ?>
                Non renseigné
              <?php endif; ?>
            </td>
          </tr>
        </tbody>
      </table>
    <?php else: ?>
      <p class="font-weight-bold">Non renseigné</p>
    <?php endif; ?>
    <p class="font-italic mt-3">Attention, vous pouvez modifier le statut de votre candidature uniquement jusqu'au vendredi précédent le premier tour des élections.</p>
  </div>
  <?php if ($candidate['modify'] == 1): ?>
    <div class="card-footer d-flex justify-content-around">
      <a href="<?= base_url() ?>dashboard-mp/elections/legislatives-2022/modifier" class="btn btn-primary">Modifier le statut de ma candidature</a>
    </div>
  <?php endif; ?>
</div>
