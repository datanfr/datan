<!-- Donation Modal -->
<div class="modal fade" id="donationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center px-4 pb-4">
        <h5 class="font-weight-bold mb-3">Vous revenez souvent sur Datan 👋</h5>
        <p>Vous avez lu <strong id="modal-page-count"></strong> pages ce mois-ci — merci de votre fidélité !</p>
        <p>Datan est gratuit et indépendant. Si le site vous est utile, pensez à nous soutenir.</p>
        <a href="<?= base_url() ?>soutenir" class="btn btn-info text-white w-100 mb-2">Soutenir Datan</a>
        <button type="button" class="btn btn-link text-muted" data-dismiss="modal">Peut-être plus tard</button>
      </div>
    </div>
  </div>
</div>