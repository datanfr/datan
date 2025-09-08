<div class="modal shadow-lg" tabindex="-1" role="dialog" id="general-modal">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <p class="modal-title mb-0">🗳️ Vote de confiance à l'Assemblée nationale</p>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Le Premier ministre François Bayrou sollicite aujourd'hui la confiance de l'Assemblée nationale. S'il n'obtient pas une majorité de votes favorables, il devra remettre la <b>démission de son gouvernement</b>.</p>
        <div class="alert alert-danger" role="alert">
          Le résultat sera publié sur Datan dès qu'il sera mis en ligne dans l'<i>open data</i> de l'Assemblée.
        </div>
        <p class="subtitle mb-0">Déroulé de la journée</p>
        <ul class="a">
          <li>Discours de politique générale du Premier ministre (15h)</li>
          <li>Réponses des représentants des groupes politiques</li>
          <li>Vote des députés : résultats attendus vers 19h</li>
        </ul>
        <p class="subtitle mb-0">Règles du vote</p>
        <p>
          La confiance se joue à la majorité simple : il suffit d'avoir plus de voix favorables que de votes contre. Les abstentions profitent au Premier ministre, mais selon les prévisions, celui-ci ne devrait pas obtenir de majorité.
        </p>
        <p class="mb-1">
          <a class="" href="<?= base_url() ?>outils/coalition-simulateur">👉 Notre simulateur de coalition</a>
        </p>
        <p><a href="<?= base_url() ?>outils/coalition-simulateur">👉 Notre analyse sur la censure du gouvernement Bayrou</a></p>
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