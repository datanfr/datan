<div class="container-fluid mb-5" id="container-always-fluid">
    <div class="row">
        <div class="container">
            <div class="row bloc-titre">
                <div class="col-12">
                    <h1><?= $title ?></h1>
                </div>
            </div>
            <div class="row row-grid mt-5">
                <div class="col-md-7">
                    <p>Un dossier législatif regroupe l'ensemble des travaux parlementaires d'un texte de loi depuis son dépôt jusqu'à son adoption, en passant par les débats et les amendements. Un texte législatif peut être par exemple un projet de loi (proposé par le Gouvernement) ou une proposition de loi (proposée par un ou plusieurs députés).</p>
                    <p>L'équipe de Datan a répertorié <?= count($dossiers) ?> dossiers législatifs ayant fait l'objet d'un vote pendant la <?= $legislature ?>ème législature.</p>
                    <p>Découvrez une présentation simplifiée des dossiers législatifs ayant fait l'objet de votes à l'Assemblée nationale. Suivez l'évolution des projets et propositions de loi, depuis leur dépôt jusqu'aux scrutins, pour comprendre les décisions qui façonnent notre législation.</p>
                </div>
                <div class="col-md-3 offset-md-1">
                    <h3><?= $legislature == legislature_current() ? 'Historique' : 'Toutes les législatures' ?></h3>
                    <p>La législature actuelle est la <?= legislature_current() ?><sup>ème</sup> législature. Elle a débuté en 2024, à la suite des <a href="<?= base_url() ?>elections/legislatives-2024">élections législatives</a>, et se terminera en 2029.</p>
                    <?php if ($legislature == legislature_current()): ?>
                        <p>Découvrez les dossiers des législatures précédentes.</p>
                    <?php else: ?>
                        <p>Découvrez les dossiers de toutes les législatures.</p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-center flex-wrap">
                        <?php if ($legislature != legislature_current()): ?>
                            <a href="<?= base_url() ?>dossiers" class="btn btn-secondary my-2">17<sup>ème</sup> législature</a>
                        <?php endif; ?>
                        <a href="<?= base_url() ?>dossiers/legislature-16" class="btn btn-secondary my-2 mx-2">16<sup>ème</sup> législature</a>
                        <a href="<?= base_url() ?>dossiers/legislature-15" class="btn btn-secondary my-2 mx-2">15<sup>ème</sup> législature</a>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12">
                    <h2>Les derniers dossiers en discussion à l'Assemblée</h2>
                    <?php foreach($dossiers_last as $dossier): ?>
                        <p>
                            <?= $dossier['titreChemin'] ?> -
                            <?= $dossier['legislature'] ?> -
                            vote n° <?= $dossier['voteNumero'] ?> -
                            date = <?= $dossier['dateScrutin'] ?> - 
                            titre = <?= $dossier['titre'] ?> -
                            <a href="<?= base_url() ?>dossiers/legislature-<?= $dossier['legislature'] ?>/<?= $dossier['titreChemin'] ?>">link</a>
                        </p>
                    <?php endforeach ?>
                </div>                
            </div>
            <div class="row mt-5">
                <div class="col-12">
                    <h2>L'ensemble des <?= count($dossiers) ?> dossiers de la <?= $legislature ?>ème législature</h2>
                    <?php foreach($dossiers as $dossier): ?>
                        <p>
                            <?= $dossier['titreChemin'] ?> -
                            <?= $dossier['legislature'] ?> -
                            vote n° <?= $dossier['voteNumero'] ?> -
                            date = <?= $dossier['dateScrutin'] ?> - 
                            titre = <?= $dossier['titre'] ?> -
                            <a href="<?= base_url() ?>dossiers/legislature-<?= $dossier['legislature'] ?>/<?= $dossier['titreChemin'] ?>">link</a>
                        </p>
                    <?php endforeach ?>
                </div>                
            </div>
        </div>
    </div>
</div>