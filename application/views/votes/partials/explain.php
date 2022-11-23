<?php
$widthCol = isset($explain[0]) ? 4 : 6;
?>
<div class="container-fluid py-5" id="pattern_background">
    <div class="container pg-vote-individual">
        <div class="row explain">
            <div class="col-12 mb-4">
                <h2 class="text-center">La parole aux députés</h2>
                <p class="mt-5 text-center">Sur <b>Datan</b>, les députés peuvent donner leur explication de vote. Pourquoi ont-ils voté pour ou contre ce texte ? Découvrez ci-dessous les explications des députés.</p>
            </div>
            <div class="col-lg-<?= $widthCol ?>">
                <p class="text-center font-weight-bold sort-adopté h5">LES DÉPUTÉS POUR</p>
                <div class="card mt-4 py-0">
                    <div class="card-body read-more-container py-0 mt-3">
                        <?php if (isset($explain[1])) : ?>
                            <?php foreach ($explain[1] as $exp) :
                                $this->load->view('votes/partials/explain_text.php', array('exp' => $exp));
                            endforeach ?>
                            <p class="read-more-button"><a href="#" class="btn btn-primary">Lire plus</a></p>
                        <?php else : ?>
                            <p><i>Aucun député <b>pour</b> ne sait encore exprimé.</i></p>
                        <?php endif ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-<?= $widthCol ?> mt-5 mt-lg-0">
                <p class="text-center font-weight-bold sort-rejeté h5">LES DÉPUTÉS CONTRE</p>
                <div class="card mt-4 py-0">
                    <div class="card-body read-more-container py-0 mt-3">
                        <?php if (isset($explain[-1])) : ?>
                            <?php foreach ($explain[-1] as $exp) :
                                $this->load->view('votes/partials/explain_text.php', array('exp' => $exp));
                            endforeach ?>
                            <p class="read-more-button"><a href="#" class="btn btn-primary">Lire plus</a></p>
                        <?php else : ?>
                            <p><i>Aucun député <b>contre</b> ne sait encore exprimé.</i></p>
                        <?php endif ?>
                    </div>
                </div>
            </div>
            <?php if (isset($explain[0])) : ?>
                <div class="col-lg-<?= $widthCol ?> mt-5 mt-lg-0">
                    <p class="text-center font-weight-bold sort-abstention h5">LES DÉPUTÉS ABSTENUS</p>
                    <div class="card mt-4 py-0">
                        <div class="card-body read-more-container py-0 mt-3">
                            <?php foreach ($explain[0] as $exp) :
                                $this->load->view('votes/partials/explain_text.php', array('exp' => $exp));
                            endforeach ?>
                            <p class="read-more-button"><a href="#" class="btn btn-primary">Lire plus</a></p>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
