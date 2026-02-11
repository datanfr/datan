<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="height: 14em">
  <div class="container d-flex flex-column justify-content-center py-2">
    <div class="row">
      <div class="col-12">
        <h1><?= $title ?></h1>
      </div>
    </div>
  </div>
</div>
<div class="container my-3 py-4 pg-page pg-dons">
  <div class="row">
    <div class="col-lg-8 col-md-6 order-2 order-md-1">
      <h2 class="mb-4">En quelques mots</h2>
      <p>
        <b>Datan</b> est un outil indépendant visant à <b>rendre compte de l’activité des députés</b>. En mettant l'accent sur les <b>votes</b>, nous permettons aux citoyens d'accéder facilement aux positions réelles des parlementaires, un élément essentiel dans une démocratie représentative.
      </p>
      <p>
         Sans publicité, sans financement partisan, nous dépendons uniquement des dons des utilisateurs. <b>Datan</b> étant un projet géré par des bénévoles, ces dons permettent de couvrir les coûts de développement, d'hébergement et de maintenance du site.
      </p>
      <div class="d-flex my-5 justify-content-center">
        <div class="card card-figures py-4 w-100">
          <div class="d-flex flex-column flex-lg-row justify-content-around text-center">
            <div class="d-flex flex-column align-items-center">
              <div class="figure"><?= $total_deputes ?></div>
              <div class="legend">Députés suivis</div>
            </div>
            <div class="d-flex flex-column align-items-center mt-4 mt-lg-0">
              <div class="figure">4</div>
              <div class="legend">Législatures</div>
            </div>
            <div class="d-flex flex-column align-items-center mt-4 mt-lg-0">
              <div class="figure"><?= $total_votes ?></div>
              <div class="legend">Votes décryptés</div>
            </div>
          </div>
        </div>
      </div>
      <h2 class="mt-4">🔍 Ce que vous trouvez sur Datan</h2>
      <div class="row mt-4">
        <div class="col-md-6 col-12">
          <div class="card card-explain p-3">
            <div class="card-bordy">
              <div class="title">Votes</div>
              <div class="legend">Découvrez les positions de vote de votre député.</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-12">
          <div class="card card-explain p-3 mt-3 mt-md-0">
            <div class="card-bordy">
              <div class="title">Députés</div>
              <div class="legend">Retrouvez les 577 députés de l'Assemblée nationale.</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-12">
          <div class="card card-explain p-3 mt-3">
            <div class="card-bordy">
              <div class="title">Groupes politiques</div>
              <div class="legend">Explorez les <?= $total_groups ?> groupes de l'Assemblée nationale.</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-12">
          <div class="card card-explain p-3 mt-3">
            <div class="card-bordy">
              <div class="title">Statistiques</div>
              <div class="legend">Accédez à des statistiques sur les votes et députés.</div>
            </div>
          </div>
        </div>        
      </div>
      <h2 class="mt-5 mb-4">💡 Pourquoi soutenir Datan ?</h2>
      <p><b>Défendre la transparence</b>. Nous soutenir, c'est défendre la transparence, indispensable au bon fonctionnement de la démocratie. En rendant accessibles les votes des députés, nous permettons à chaque citoyen de comprendre les actions des parlementaires et groupes politiques.</p>
      <p><b>Totalement gratuit et indépendant</b>. Datan est un site entièrement gratuit, indépendant et neutre. Nous ne recevons aucun financement de la part de partis politiques, d'entreprises ou de collectivités publiques. Nos ressources reposent sur la générosité des dons individuels.</p>
      <p><b>Budget de fonctionnement</b>. Le budget de fonctionnement de Datan s'élève actuellement à environ 400€ par an. Bien que le projet repose exclusivement sur le bénévolat, augmenter nos ressources nous permettrait de rémunérer une partie de ce travail et ainsi accroître nos activités de vulgarisation et pérenniser le projet.</p>
      <h2 class="mt-4">Contactez-nous</h2>
      <p>info[at]datan.fr</p>      
    </div>
    <div class="col-lg-4 col-md-6 order-1 order-md-2 mb-4">
      <h2 class="mb-4">Soutenez Datan</h2>
      <iframe id="haWidgetLight" allowtransparency="true" allow="payment" scrolling="auto" src="https://www.helloasso.com/associations/datan/formulaires/1/widget?view=form" style="width: clamp(300px, 100%, 26rem); margin: 0 auto; border: none;" onload="window.addEventListener('message', e => {const dataHeight = e.data.height; const haWidgetElement = document.getElementById('haWidgetLight'); haWidgetElement.height = dataHeight + 'px';})"></iframe>
    </div>
  </div>
</div>