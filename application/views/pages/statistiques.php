<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="height: 14em">
  <div class="container d-flex flex-column justify-content-center py-2">
    <div class="row">
      <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
        <h1><?= $title ?></h1>
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
            <b>Datan</b> réutilise les données de l'Assemblée nationale, qui sont <a href="http://data.assemblee-nationale.fr/licence-ouverte-open-licence" target="_blank" rel="nofollow noopener">distribuées sous la licence « Licence Ouverte / Open Licence »</a>.
          </p>
          <p>
            Les données mises en forme par Datan, et les statistiques que nous élaborons, visent également à être disponibles et distribuées en licence ouverte. Nous publions sur le site <b>data.gouv.fr</b> et mettons à jour de façon hebdomadaire les jeux de données suivants :
          </p>
          <ul>
            <li>
              <a href="https://www.data.gouv.fr/fr/datasets/deputes-actifs-de-lassemblee-nationale-informations-et-statistiques/" target="_blank" rel="nofollow noopener">Députés actifs de l'Assemblée nationale - Informations et statistiques</a>
            </li>
            <li>
              <a href="https://www.data.gouv.fr/fr/datasets/groupes-politiques-actifs-de-lassemblee-nationale-informations-et-statistiques/" target="_blank" rel="nofollow noopener">Groupes actifs de l'Assemblée nationale - Informations et statistiques</a>
            </li>
            <li>
              <a href="https://www.data.gouv.fr/fr/datasets/historique-des-deputes-de-lassemblee-nationale-depuis-2002-informations-et-statistiques/" target="_blank" rel="nofollow noopener">Historique des députés de l'Assemblée nationale (depuis 2002) - Informations et statistiques</a>
            </li>
            <li>
              <a href="https://www.data.gouv.fr/fr/datasets/historique-des-groupes-politiques-de-lassemblee-nationale-depuis-2012-informations-et-statistiques/" target="_blank" rel="nofollow noopener">Historique des groupes politiques de l'Assemblée nationale (depuis 2012) - Informations et statistiques</a>
            </li>

          </ul>
          <h2 id="participation" class="anchor">La participation aux votes</h2>
          <p>
            Le vote est une activité essentielle pour un député : les élus votent pour ou contre des projets de loi qui auront un impact direct sur la vie des citoyens.
          </p>
          <p>
            Cependant, les députés ont plusieurs activités (contrôle du gouvernement, écriture d'amendements, présence en circonscription) et ne sont donc pas tout le temps présent dans l'hémicycle. De plus, contrairement au <a href='https://www.europarl.europa.eu/about-parliament/fr/organisation-and-rules/how-plenary-works' title='Les votes au Parlement européen' target="_blank" rel="nofollow noopener">Parlement européen</a>, les votes dans l'hémicycle se déroulent à n'importe quel moment de la semaine, souvent en même temps que d'autres réunions importantes pour les députés, comme les réunions de commissions parlementaires.
          </p>
          <p>
            Nous avons développé <a href="<?= base_url() ?>statistiques/deputes-participation">trois scores de participation</a>. Il s'agit de pourcentages. Ainsi, si un député participe à 10 votes sur 100, il aura un score de participation de 10 %.
          </p>
          <ul>
            <li><b>Scrutins solennels</b>. Le premier score, mis en avant sur les pages des députés, est le taux de participation aux scrutins solennels. Les scrutins solennels sont les votes les plus importants et concernent des projets de loi significatifs et fortement discutés dans les médias. Pour ces votes, le jour et l'heure du vote sont connus à l'avance, favorisant ainsi la présence des parlementaires dans l'hémicycle. Il est attendu d'un député qu'il participe à la majorité de ces scrutins.</li>
            <li><b>Spécialisation du député.</b> Le deuxième score ne prend en compte que les votes en lien avec le domaine de spécialisation d'un député. Par exemple, un député avec un score de 100% aura participé, en séance publique, à tous les scrutins sur des textes qui ont été précédemment examinés dans sa commission parlementaire. Ce sont sur ces votes que les élus ont un intérêt et une expertise particulière, et sont donc plus susceptibles de participer aux travaux parlementaires.</li>
            <li><b>Tous les votes</b>. Pour ce dernier score, nous prenons en compte tous les votes. Ce score est en moyenne assez faible, étant donné que beaucoup de votes dans l'hémicycle ont lieu en même temps que des réunions de commissions parlementaires.</li>
          </ul>
          <p>
            Le <b>taux de participation des groupes politiques</b> représente la moyenne des taux de participation de ses députés membres et apparentés. Nous prenons en compte le score de participation aux scrutins solennels.
          </p>
          <p>
            Par exemple, prenons un groupe parlementaire fictif, qui se nomme <i>Vive la Politique</i>. Ce groupe compte 5 membres. Un des députés membre du groupe participe à 20% des scrutins solennels, un autre à 15%, un autre à 30%, un autre à 5%, et un autre à 35%. Le taux de participation du groupe est la moyenne des taux individuels. Par exemple, <i>(20+15+30+5+35)/5 = 21%.</i> Le taux de participation du groupe est donc de 21%. Aurement dit, les députés membres du groupe <i>Vive la Politique</i> participent <i>en moyenne</i> à 21% des scrutins solennels.
          </p>
          <h2 id="loyalty" class="anchor">La proximité des politiques à leur groupe</h2>
          <p>
            Les députés membres d'un groupe peuvent soit voter avec la ligne majoritaire de leur groupe, soit contre. Le nombre de fois où ils votent avec la ligne de leur groupe détermine leur proximité.
          </p>
          <p>
            La ligne politique du groupe politique correspond à la position qui a dominé parmi les députés qui en sont membres. Par exemple, si le nombre de députés membres du groupe <i>Vive la Politique</i> votant "pour" une proposition de loi est supérieur au nombre de députés membres du groupe votant "contre" ainsi qu'au nombre de députés membres du groupe s'abstenant, le groupe est considéré comme ayant voté "pour".
          </p>
          <p>
            Pour chaque vote auquel un député a pris part, le vote du député est ainsi comparé à la position de son groupe politique. Si la députée Julie Dupont a voté "pour" une proposition de loi, et son groupe parlementaire a également voté "pour", elle est <b>loyale</b>. Par contre, si Julie Dupont a voté "contre" un amendement, alors que son groupe a voté "pour", elle est <b>rebelle</b>.
          </p>
          <p>
            Le <b>taux de proximité d'un député à son groupe</b> (ou taux de loyauté) est le <b>pourcentage de votes</b> où le député a voté avec la ligne majoritaire du groupe. Si Julie Dupont a participé à 100 votes depuis son élection, et elle a voté avec son groupe sur 85 votes, alors son taux de loyauté est de 85%.
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
          <h2 id="ageMoyen">L'âge moyen en France</h2>
          <p>
            Sur certaines pages, nous comparons l'âge des groupes parlementaires, ou l'âge moyen des députés, avec la moyenne d'âge de la population française. Les personnes attachant de l'importance à la représentativité « sociale » du parlement estiment que les députés ne doivent pas forcément être plus vieux ou plus jeunes que l'ensemble des Français.
          </p>
          <p>
            Pour mesurer la moyenne d'âge de la population française, nous avons enlevé les citoyens âgés de moins de 18 ans, qui ne peuvent pas être élus députés. Ainsi, l'âge des députés est comparé à la moyenne d'âge des Français en âge d'être éligible.
          </p>
          <p>
            Ainsi, alors que la moyenne d'âge de toute la population est de <?= round(mean_age_france_all()) ?> ans (<a href="https://www.insee.fr/fr/statistiques/2381476" target="_blank" rel="nofollow noopener">source insee</a>), la moyenne des Français de plus de 18 ans est de <?= round(mean_age_france()) ?> ans.
          </p>
          <p>
            Pour le calculer, nous avons récupéré la pyramide des âges, un document avec le nombre d'habitants en France selon leur âge (<a href="https://www.insee.fr/fr/statistiques/2381472#tableau-figure1" target="_blank" rel="nofollow noopener">source insee</a>). Nous avons ensuite calculé la moyenne d'âge en ne prenant en compte que les individus ayant plus de 18 ans. Pour découvrir le document avec le calcul, <a href="https://docs.google.com/spreadsheets/d/17pf7I0vN_yIl7lnebXhZSKDRaE6j31qzRX77Cx1SYD8/edit#gid=1699168638" target="_blank" rel="nofollow noopener">cliquez ici</a>.
          </p>
          <h2>Contactez-nous</h2>
          <p>Pour plus d'information, envoyez-nous un email : info[at]datan.fr</p>
        </div>
      </div>
    </div>
  </div>
</div>
