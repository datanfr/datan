<div class="modal fade" id="voteDatanRequested" tabindex="-1" role="dialog" aria-labelledby="voteDatanRequestedLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <?= form_open('voteDatanRequest', array('id'=> 'voteDatanRequestedForm')); ?>
        <div class="modal-header">
          <h2 class="modal-title" id="voteDatanRequestedLabel">Demandez une explication sur ce vote</h2>
          <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </span>
        </div>
        <div class="modal-body">
          <p>Vous souhaitez que <b>l'équipe de Datan</b> contextualise et décrypte ce vote. Pour être informé quand le vote sera décrypté, laissez-nous votre adresse email.</p>
          <div class="form-group">
            <input type="hidden" name="legislature" value="<?= $legislature ?>" />
            <input type="hidden" name="voteNumero" value="<?= $vote['voteNumero'] ?>" />
          </div>
          <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Votre email">
          </div>
          <div class="form-check mt-1">
            <input type="checkbox" name="newsletter" class="form-check-input" id="voteDatanRequest">
            <label class="form-check-label">Vous souhaitez recevoir plus d'informations de notre part ? Inscrivez-vous à nos newsletters !</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary font-weight-bold">Demandez une explication</button>
        </div>
      <?= form_close() ?>
      <div id="success">
        <div class="modal-header">
          <h2 class="modal-title">Félicitations</h2>
          <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </span>
        </div>
        <div class="modal-body">
          <p>Nous vous tiendrons informé quand ce vote sera décrypté :)</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        </div>
      </div>
      <div id="fail">
        <div class="modal-header">
          <h2 class="modal-title">Quelque chose s'est mal passé</h2>
          <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </span>
        </div>
        <div class="modal-body">
          <p>Contactez-nous si vous rencontrez ce problème : info@datan.fr</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>
</div>
