<div class="container pg-parrainages">
  <div class="row bloc-titre">
    <div class="col-12">
      <h1><?= $title ?></h1>
    </div>
  </div>
</div>
<div class="container-fluid pg-parrainages infosIndividual py-5">
  <div class="container test-border">
    <div class="row">
      <div class="col-md-8 col-lg-7 my-4 test-border">
        <h2>Comment fonctionne le parrainage des candidats à la présidentielle ?</h2>
        <p>Les personnes souhaitant être candidates à l'élection présidentielle doivent réunir des parrainages de parlemen ou d'élus locaux. Pour voir son élection valider par le Conseil constitutionnel, il faut réunir au moins 500 parrainages venant d'au moins 30 départements différents.</p>
        <p>Le système de parrainage a été mis en place afin d'éviter la multiplication des candidatures.</p>
        <p><b>Qui peut accordé son parrainage ?</b></p>
        <p>Plus de 40 000 élus ont la possibilité d'accorder leur parrainage à un candidat à l'élection présidentielle. Il s'agit des députés, des sénateurs, des élus régionaux et départementaux, ou encore des maires.</p>
      </div>
      <div class="col-md-4 col-lg-5 test-border d-flex align-items-center">
        <div class="test-border">
          <div class="card">
            <h3 class="card-header">Candidats sélectionnés</h3>
            <div class="card-body">
              <p class="font-weight-bold">Candidats ayant récolté plus de 500 signatures</p>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th scope="col"></th>
                    <th scope="col">Candidat</th>
                    <th scope="col">Nombre de parrainages</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; ?>
                  <?php foreach ($candidates_selected as $candidate): ?>
                    <tr>
                      <th><?= $i ?></th>
                      <td><?= $candidate['candidat'] ?></td>
                      <td class="text-center"><?= $candidate['n'] ?></td>
                    </tr>
                    <?php $i++; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <div class="d-flex justify-content-center">
                <a href="#" class="btn btn-primary">Plus d'infos</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container pg-parrainages mt-4 mb-5 test-border">
  <div class="row">
    <!-- https://www.lemonde.fr/les-decodeurs/article/2022/02/01/election-presidentielle-2022-visualisez-les-parrainages-obtenus-par-les-candidats_6111902_4355770.html -->
    <div class="col-12 test-border">
      <h2 class="my-4"><span class="text-primary">400n</span> députés ont parrainés un candidat à la présidentielle</h2>
      <p>Texte ici de présentation !</p>
      <p>Chart</p>
    </div>
    <div class="col-12 test-border">
      <h2 class="my-4">Les parrainages à la présidentielle par groupe politique</h2>
      <p>Texte ici de présentation !</p>
      <p>Différents charts (un par groupe)</p>
    </div>
    <div class="col-12 test-border">
      <h2 class="my-4">Découvrez les parrainages accordez par tous les députés</h2>
      <p>Texte ici de présentation !</p>
      <p>Tableau (syle vote)</p>
    </div>
  </div>
</div>
