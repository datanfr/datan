<div class="card card-election my-3">
    <div class="card-body d-flex flex-column justify-content-center align-items-center">
        <h2 class="d-block card-title">
            <a href="<?= base_url(); ?>elections/<?= mb_strtolower($election['slug']) ?>" class="stretched-link no-decoration"><?= $election['libelleAbrev'] ?><br><?= $election['dateYear'] ?></a>
        </h2>
        <span class="mt-3">1<sup>er</sup> tour : <?= $election['dateFirstRoundFr'] ?></span>
        <?php if ($election['dateSecondRound']): ?>
            <span>2<sup>nd</sup> tour : <?= $election['dateSecondRoundFr'] ?></span>
        <?php endif; ?>
    </div>
    <?php if ($election['candidates']): ?>
        <div class="card-footer d-flex justify-content-center align-items-center">
            <?php if($election['state'] == 2): ?>
                <span class="font-weight-bold"><?= $election['electedN'] ?> député<?= $election['electedN'] > 1 ? "s" : "" ?> élu<?= $election['electedN'] > 1 ? "s" : "" ?></span>
            <?php elseif($election['state'] == 1): ?>
                <span class="font-weight-bold"><?= $election['secondRoundN'] ?> député<?= $election['secondRoundN'] > 1 ? "s" : "" ?> au second tour</span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card-footer bg-transparent"></div>
    <?php endif; ?>
</div>