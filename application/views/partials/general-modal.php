<div class="modal shadow-lg" tabindex="-1" role="dialog" id="general-modal">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <p class="modal-title mb-0">🔴 Les députés votent contre la confiance au François Bayrou</p>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Une majorité de députés <b>a voté contre</b> la confiance au Premier ministre François Bayrou. Sur 558 suffrages exprimés, 364 députés ont voté contre et 194 députés ont voté pour. Le Premier ministre doit désormais remettre la démission de son gouvernement.</p>
        <div class="alert alert-danger" role="alert">
          Le résultat sera publié sur Datan dès qu'il sera mis en ligne dans l'<i>open data</i> de l'Assemblée.
        </div>
        <p class="subtitle mb-0">En savoir plus</p>
        <p class="mb-1">
          👉
          <a class="" href="<?= base_url() ?>outils/coalition-simulateur">Notre simulateur de coalition</a>
        </p>
        <p>
          👉
          <a href="<?= base_url() ?>outils/coalition-simulateur">Notre analyse sur la censure du gouvernement Bayrou</a></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    var myModal = new bootstrap.Modal(document.getElementById('general-modal'));
    myModal.show();
  });
</script>