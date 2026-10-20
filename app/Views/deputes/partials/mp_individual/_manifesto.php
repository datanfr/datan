<!-- BLOC PROFESSIONS DE FOI -->
<?php if ($professions_foi): ?>
  <div class="mt-5">
    <h2 class="mb-4 title-center">Ses professions de foi</h2>
    <p>Une <span class="font-weight-bold text-primary">profession de foi</span> est un document rédigé par un candidat à une élection. Dans ce document, le candidat se présente et expose ses idées et son programme qu'il ou elle souhaite défendre et mettre en place en cas d'élection. Avec une profession de foi, les candidats tentent de se démarquer des autres candidats, aussi bien sur le fond que la forme.</p>
    <p>En France, les professions de foi sont envoyées par courrier au domicile des personnes inscrites sur les listes électorales.</p>
    <table class="table table-bordered mt-4">
      <caption class="sr-only">Les professions de foi de <?= $title ?></caption>
      <thead class="thead-dark">
        <tr>
          <th scope="col">Élection</th>
          <th scope="col"><span class="sr-only">1er tour</span></th>
          <th scope="col"><span class="sr-only">2nd tour</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($professions_foi as $key => $value): ?>
          <tr class="bg-white">
            <th scope="row" class="font-weight-bold align-middle"><?= $value['election'] ?></th>
            <td class="text-center align-middle">
              <?php if ($value['round1']): ?>
                <a class="btn btn-outline-primary" href="<?= $value['round1'] ?>" target="_blank">
                  <?= file_get_contents(FCPATH . '/assets/imgs/icons/arrow_external_right.svg') ?>
                  Profession 1er tour
                </a>
              <?php endif; ?>
            </td>
            <td class="text-center align-middle">
              <?php if ($value['round2']): ?>
                <a class="btn btn-outline-primary" href="<?= $value['round2'] ?>" target="_blank">
                  <?= file_get_contents(FCPATH . '/assets/imgs/icons/arrow_external_right.svg') ?>
                  Profession 2nd tour
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>