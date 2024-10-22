<div class="container">   
    <div class="row row-grid bloc-titre mb-5">
        <div class="col-lg-4 col-md-5 mb-4 mb-md-0">
            <h1>Les dossiers législatifs à l'Assemblée nationale</h1>
        </div>
        <div class="col-lg-4 col-md-7 mt-md-0">
            <p>
                Un dossier législatif regroupe l'ensemble des travaux parlementaires d'un texte de loi depuis son dépôt jusqu'à son adoption, en passant par les débats et les amendements. Un texte législatif peut être par exemple un projet de loi (proposé par le Gouvernement) ou une proposition de loi (proposée par un ou plusieurs députés).
            </p>
            <p>
                L'équipe de Datan a répertorié <?= count($dossiers) ?> dossiers législatifs ayant fait l'objet d'un vote pendant la 17ème législature.
            </p>
            <p>
                Découvrez une présentation simplifiée des dossiers législatifs ayant fait l'objet de votes à l'Assemblée nationale. Suivez l'évolution des projets et propositions de loi, depuis leur dépôt jusqu'aux scrutins, pour comprendre les décisions qui façonnent notre législation.
            </p>
        </div>        
    </div>
    <div class="row">
        <div class="col-12">
            count = <?= count($dossiers) ?>
            <?php $i = 1 ?>
            <?php foreach($dossiers as $dossier): ?>
            <p>
                id = <?= $dossier['id'] ?> -     
                <?= $dossier['titreChemin'] ?> -
                <?= $dossier['legislature'] ?> -
                vote n° <?= $dossier['voteNumero'] ?> -
                date = <?= $dossier['dateScrutin'] ?> - 
                titre = <?= $dossier['titre'] ?>
            </p>
            <?php endforeach ?>
        </div>
    </div>

</div>