<h2 class="my-4">Résultats des élections de 2021</h2>
<p>Après les élections, les conseillers départementaux élisent le président du conseil départemental. Le président est souvent issu de la liste qui est arrivé en tête lors des élections.</p>
<p>Le président élu, qui se retrouve à la tête du conseil département, est chargé de l'administration et a la charge des dépenses et des recettes.</p>
<p>Découvrez sur cette carte la couleur politique des différents départements après les élections de 2021.</p>
<div class="map-container my-5">
  <div class="jvmap-smart" id="map-departements"></div>
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
