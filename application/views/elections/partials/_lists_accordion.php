<div class="mt-4" id="listesAccordion">
    <?php foreach($listes as $liste): ?>
        <div class="liste-card mb-3">
        <div class="liste-header d-flex align-items-center px-4 py-3" data-toggle="collapse" data-target="#liste<?= $liste['numero_panneau'] ?>">
            <div class="partie-dot mr-3" style="background-color: <?= $liste['nuance_color'] ?>;"></div>
            <div class="flex-grow-1">
                <div class="liste-tete"><?= $liste['tete_de_liste'] ?></div>
                <div class="liste-meta">
                <?php if($liste['nuance_edited']): ?>
                    <span class="nuance"><?= $liste['nuance_edited'] ?></span>
                    <span class="liste-separator">·</span>
                <?php endif; ?>
                <span><?= $liste['libelle_liste'] ?></span>                    
                </div>
            </div>
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
            </svg>
        </div>
        <div id="liste<?= $liste['numero_panneau'] ?>" class="collapse">
            <div class="liste-candidates p-4">
            <?php foreach($liste['candidats'] as $candidat): ?>
                <div class="candidate-row py-2">
                <?= $candidat['ordre'] ?>. <?= $candidat['prenom']?> <?= $candidat['nom']?>
                <?php if($candidat['code_personnalite'] == 'DEP'): ?>
                    <span class="badge badge-primary ml-2">Député<?= $candidat['sexe'] == 'F' ? 'e' : '' ?></span>
                <?php endif; ?>
                <?php if($candidat['code_personnalite'] == 'SEN'): ?>
                    <span class="badge badge-primary ml-2"><?= $candidat['sexe'] == 'F' ? 'Sénatrice' : 'Sénateur' ?></span>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        </div>
    <?php endforeach; ?>
</div>