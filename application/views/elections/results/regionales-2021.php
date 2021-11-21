<h2 class="my-4">Résultats des élections de 2021</h2>
<p>Dans les conseils régionaux, les partis politiques qui arrivent en têtes aux élections reçoivent la majorité des sièges et en prennent la présidence. C'est donc la liste arrivée en tête qui se retrouve à la tête de la région.</p>
<p>Découvrez sur cette carte la couleur politique des différentes régions après le second tour des élections de 2021.</p>
<div class="map-container my-5">
  <div class="jvmap-smart" id="map-regions"></div>
</div>
<div class="row my-4">
  <?php foreach ($mapLegend as $x): ?>
    <div class="map-container-ledend col-6">
      <div class="d-flex my-2">
        <div class="color" style="background-color: <?= $x['color'] ?>"></div>
        <span class="ml-4"><?= $x['party'] ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>
