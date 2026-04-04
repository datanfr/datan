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
        <h5 class="font-weight-bold mb-4">Vous revenez souvent sur Datan 👋</h5>
        <p class="mb-4">Merci d'utiliser Datan ! Vous avez lu XX pages ce mois-ci. Si vous l'utilisez régulièrement pour suivre les votes des députés, pensez à nous soutenir.</p>
        <a href="<?= base_url() ?>soutenir" class="btn btn-info text-white w-100 mb-2">Soutenir Datan</a>
        <button type="button" class="btn btn-link text-muted" data-dismiss="modal">Peut-être plus tard</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
      var THRESHOLD = 10;
      var ONE_WEEK_MS = 7 * 24 * 60 * 60 * 1000;

      function getCookie(name) {
          var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
          return match ? decodeURIComponent(match[2]) : null;
      }

      function setCookie(name, value, ms) {
          var expires = new Date(Date.now() + ms).toUTCString();
          document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/';
      }

      // Track monthly pages
      var now = new Date();
      var currentMonth = now.getFullYear() + '-' + (now.getMonth() + 1);
      var cookieRaw = getCookie('datan_monthly');
      var data = cookieRaw ? JSON.parse(decodeURIComponent(cookieRaw)) : null;

      if (!data || data.month !== currentMonth) {
          data = { month: currentMonth, count: 0 };
      }

      data.count++;
      setCookie('datan_monthly', JSON.stringify(data), 1000 * 60 * 60 * 24 * 60);

      // Update footer counter
      var el = document.getElementById('monthly-pages-visited-count');
      if (el) el.textContent = data.count;

      // Modal logic
      if (data.count < THRESHOLD) return;

      var lastShown = getCookie('datan_modal_shown');
      if (lastShown && (Date.now() - parseInt(lastShown, 10)) < ONE_WEEK_MS) return;

      setCookie('datan_modal_shown', Date.now(), ONE_WEEK_MS);

      $(document).ready(function () {
          $('#modal-page-count').text(data.count);
          $('#donationModal').modal('show');
      });

  })();
</script>