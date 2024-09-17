<div class="container test-border">
    <div class="row">
        <div class="col-12">
            <?= count($dossiers) ?>
            <?php foreach($dossiers as $dossier): ?>
                <p><?= $dossier['titreChemin'] ?> - <?= $dossier['legislature'] ?></p>
            <?php endforeach ?>
        </div>
    </div>

</div>