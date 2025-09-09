<div class="modal shadow-lg" tabindex="-1" role="dialog" id="general-modal">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <p class="modal-title mb-0">🔴 Les députés votent contre la confiance à François Bayrou</p>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>
          Une majorité de députés <b>a voté contre</b> la confiance au Premier ministre François Bayrou. Sur 558 suffrages exprimés, 364 députés ont voté contre et 194 députés ont voté pour.
        </p>
        <p>
          Le Premier ministre doit désormais remettre la <b>démission de son gouvernement</b>.
        </p>
      </div>
      <div class="modal-footer d-flex justify-content-around">
        <a href="<?= base_url() ?>blog/actualite-politique/les-deputes-refusent-la-confiance-a-francois-bayrou-et-maintenant" role="button" class="btn btn-primary center" target="_blank">Lire ntore analyse</a>
        <a href="<?= base_url() ?>votes/legislature-17/vote_3054" role="button" class="btn btn-primary center" target="_blank">Découvrir le vote</a>
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