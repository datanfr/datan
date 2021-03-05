<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
  <div class="container d-flex flex-column justify-content-center py-2">
    <div class="row">
      <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
        <h1><?= mb_strtoupper($title) ?></h1>
      </div>
    </div>
  </div>
</div>
<div class="container my-3 pg-page">
  <div class="row">
    <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
      <div class="row">
        <div class="col-12">
          <h2>En quelques mots</h2>
          <p>
            <b>Datan</b> est un outil indépendant visant à <b>mieux rendre compte de l’activité parlementaire des députés</b>. Pour ce faire, l’accent est mis sur les pratiques de vote, permettant ainsi aux citoyens de comprendre le positionnement réel de leurs parlementaires.
          </p>
          <p>
            L'équipe de Datan a développé des indexes permettant de mieux comprendre comment se positionnent et agissent les députés et les groupes parlementaires quand ils votent.
          </p>
          <h2>Accès aux données</h2>
          <p>
            <b>Datan</b> réutilise les données de l'Assemblée nationale, qui sont <a href="http://data.assemblee-nationale.fr/licence-ouverte-open-licence" target="_blank">distribuées sous la licence « Licence Ouverte / Open Licence »</a>.
          </p>
          <p>
            Les données mises en forme par Datan, et les statistiques que nous élaborons, visent également à être disponibles et distribuées en licence ouverte. Nous publions sur le site <b>data.gouv.fr</b> et mettons à jour de façon hebdomadaire les jeux de données suivants :
          </p>
          <ul>
            <li>
              <a href="https://www.data.gouv.fr/fr/datasets/deputes-actifs-de-lassemblee-nationale-informations-et-statistiques/" target="_blank">Députés actifs de l'Assemblée nationale - Informations et statistiques</a>
            </li>
          </ul>
          <h2 id="participation" class="anchor">La participation aux votes</h2>
          <p>
            Pour calculer les <b>taux de participation des députés</b>, nous prenons en compte chaque vote (scrutin) auquel un député aurait pu participé lors de son mandat. Le score qui se trouve sur la page des députés représente le <i>pourcentage</i> de scrutin pour lesquels le député a participé, par rapport au total des scrutins qui ont eu lieu au cours de son mandat (pendant la 15<sup>e</sup>législature).
          </p>
          <p>
            Par exemple, prenons une députée fictive, qui se nomme Julie Dupont. Julie est députée depuis juin 2017. Depuis cette date, 200 votes ont eu lieu à l'Assemblée nationale. Julie Dupont a participé à 50 votes. Elle a donc un taux de participation de 25%. Autrement dit, elle a participé à 25% des votes (<i>50/100*100 = 25</i>).
          </p>
          <p>
            Le <b>taux de participation des groupes politiques</b> représente la moyenne des taux de participation de ses députés membres et apparentés.
          </p>
          <p>
            Par exemple, prenons un groupe parlementaire fictif, qui se nomme <i>Vive la Politique</i>. Ce groupe compte 5 membres. Un des députés membre du groupe participe à 20% des scrutins, un autre à 15%, un autre à 30%, un autre à 5%, et un autre à 35%. Le taux de participation du groupe est la moyenne des taux individuels. Par exemple, <i>(20+15+30+5+35)/5 = 21%.</i> Le taux de participation du groupe est donc de 21%. Aurement dit, les députés membres du groupe <i>Vive la Politique</i> participent <i>en moyenne</i> à 21% des scrutins.
          </p>
          <p>
            <b>Attention</b>, le taux de participation des députés, et la moyenne des groupes, ne mesure pas toute l'activité parlementaire. En plus des votes, les députés peuvent par exemple rédiger des propositions de loi, rapports, écrire des amendements, poser des questions écrites ou orales au gouvernement, etc. Les députés prennent également part aux discussions en commission et en séance plénière. De plus, les faibles taux de participation des députés s'expliquent par l'organisation interne de l'Assemblée nationale. Contrairement au <a href='https://www.europarl.europa.eu/about-parliament/fr/organisation-and-rules/how-plenary-works' title='Les votes au Parlement européen' target="_blank">Parlement européen</a>, les votes en séance plénière se déroulent à n'importe quel moment de la semaine, souvent en même temps que se tiennent d'autres réunions également importantes pour les députés, comme les réunions de commissions parlementaires.
          </p>
          <h2 id="loyalty" class="anchor">La loyauté envers son groupe</h2>
          <p>
            Les députés membres d'un groupe peuvent soit voter avec la ligne majoritaire de leur groupe, soit contre. Dans le premier cas, il est <b>loyal</b> ; dans le second, il est <b>rebelle</b>.
          </p>
          <p>
            La ligne politique du groupe politique correspond à la position qui a dominé parmi les députés qui en sont membres. Par exemple, si le nombre de députés membres du groupe <i>Vive la Politique</i> votant "pour" une proposition de loi est supérieur au nombre de députés membres du groupe votant "contre" ainsi qu'au nombre de députés membres du groupe s'abstenant, le groupe est considéré comme ayant voté "pour".
          </p>
          <p>
            Pour chaque vote auquel un député a pris part, le vote du député est ainsi comparé à la position de son groupe politique. Si la députée Julie Dupont a voté "pour" une proposition de loi, et son groupe parlementaire a également voté "pour", elle est <b>loyale</b>. Par contre, si Julie Dupont a voté "contre" un amendement, alors que son groupe a voté "pour", elle est <b>rebelle</b>.
          </p>
          <p>
            Le <b>taux de loyauté d'un député</b> est le <b>pourcentage de votes</b> où le député a voté avec la ligne majoritaire du groupe. Si Julie Dupont a participé à 100 votes depuis son élection, et elle a voté avec son groupe sur 85 votes, alors son taux de loyauté est de 85%. Le <b>taux de loyauté d'un groupe</b> est la moyenne des taux de loyauté des députés membres du groupe. Si le groupe <i>Vive la Politique</i> compte 5 membres, et que ces 5 membres ont des taux de loyauté de 80%, 90%, 95%, 100%, 70%, alors le taux de loyauté du groupe <i>Vive la Politique</i> est de 87% (<i>(80+90+95+100+70)/5 = 87</i>).
          </p>
          <p>
            <b>Attention</b>, il est important de noter que les taux de loyautés sont souvent élevés. Comme dans beaucoup de parlements, les députés français suivent dans la plupart des cas la ligne politique de leur groupe. Un député peut être amené à voter contre son groupe pour plusieurs raisons, par exemple quand son groupe est divisé ou quand la ligne du groupe va à l'encontre des intérêts et préférences de sa circonscription.
          </p>
          <h2 id="proximity" class="anchor">La proximité avec un groupe</h2>
          <p>
            Les députés et groupes politiques peuvent être plus ou moins proches des différents groupes parlementaires composant l'Assemblée nationale. Le <b>taux de proximité</b> d'un député avec un groupe, ou d'un groupe avec un autre groupe politique, représente le pourcentage de fois où les deux parties ont voté ensemble.
          </p>
          <p>
            Pour chaque vote, nous définissons la ligne politique de chaque groupe politique. Cette ligne correspond à la position qui a dominé au sein du groupe. Cette position "majoritaire" peut soit être "en faveur", "contre", ou "abstention". Par exemple, si le nombre de députés membres du groupe <i>Vive la Politique</i> votant "pour" une proposition de loi est supérieur au nombre de députés membres du groupe votant "contre" ainsi qu'au nombre de députés membres du groupe s'abstenant, le groupe est considéré comme ayant voté "pour".
          </p>
          <p>
            Les taux de proximité d'un député avec un groupe, ou d'un groupe avec un autre groupe, sont calculés sur la base de ces lignes politiques. Par exemple, pour chaque vote auquel un député a voté, nous comparons son vote ("pour", "contre", "abstention") avec la ligne politique de tous les groupes. Le taux de proximité correspond au pourcentage de fois où le député vote la même chose qu'un groupe politique. Ainsi, si la députée Julie Dupont a participé à 100 votes, et elle a voté comme le groupe <i>Vive la politique</i> sur 45 votes, alors elle aura un taux de proximité avec ce groupe de 45%.
          </p>
          <p>
            Le même calcul est effectué pour les groupes politiques. Si le groupe <i>Vive la Politique</i> a participé à 100 votes, et a voté avec le groupe <i>Vive la Culture</i> sur 75 votes, alors les deux groupes ont un taux de proximité de 75%.
          </p>
          <p>
            Pour tous les <b>députés non membres de la majorité présidentielle</b>, nous avons calculé et mis en avant sur leur page personnelle leur taux de proximité avec le groupe de la majorité. Nous avons également décidé de mettre en avant cet index pour les groupes politiques, permettant ainsi de voir d'un coup d'oeil la proximité entre un groupe donné et le groupe majoritaire à l'Assemblée.
          </p>
          <h2 id="cohesion" class="anchor">La cohésion d'un groupe</h2>
          <p>
            Le taux de cohésion d'un groupe politique mesure le degré d'unité du groupe quand il vote. Plusieurs formules existent dans la litérature académique. Nous avons choisi la mesure "Agreement Index" développé par Simon Hix, Abdul G. Noury et Gérard Roland, qui se calcule de la façon suivante : <i>Ai=(max(Y,N,A)-(0.5((Y+N+A)-max(Y,N,A))))/(Y+N+A)</i>, où Y = nombre de votes "pour", N = nombre de votes "contre", et A = nombre d'abstentions. Nous avons mesuré l'index de cohésion pour chaque groupe et chaque vote. Cet index peut prendre des valeurs comprises entre 0 et 1, 0 correspondant à un groupe complètement divisé et 1 à un groupe où tous ses députés ont voté la même chose. Le taux de cohésion qui se trouve sur les pages des groupes politiques correspond à la moyenne des taux de cohésion pour ce groupe pour tous les votes.
          </p>
          <p>
            <b>À savoir</b>, dans beaucoup de parlements, y compris à l'Assemblée nationale, les députés suivent souvent la ligne officielle du groupe politique. Autrement dit, quand il s'agit de voter, la cohésion des groupes politiques est relativement élevée. Tout d'abord, les députés membres d'un groupe politique sont généralement proches idéologiquement, et donc sont amenés à avoir la même position quand il s'agit de voter. De plus, les groupes peuvent exercer des pressions pour inciter les députés à être loyaux, autrement dit à voter comme la ligne officielle du groupe, favorisant ainsi la cohésion au sein du groupe. La mesure de cohésion proposée est donc intéressante quand elle est comparée aux taux de cohésion des autres groupes parlementaires.
          </p>
          <h2>Contactez-nous</h2>
          <p>Pour plus d'information, envoyez-nous un email : <a href="mailto:contact@datan.fr">contact@datan.fr</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
