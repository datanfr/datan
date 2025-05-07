<!-- CARD MAJORITE -->
<?php if (!in_array($depute['groupeId'], $this->groupes_model->get_all_groupes_majority()) && $depute['legislature'] != 17): ?>
    <div class="card card-statistiques my-4">
        <div class="card-body">
            <div class="row">
                <div class="col-12 d-flex flex-row align-items-center">
                    <div class="icon">
                        <?= file_get_contents(base_url() . '/assets/imgs/icons/elysee.svg') ?>
                    </div>
                    <h3 class="ml-3 text-uppercase">
                        Proximité avec la majorité gouvernementale
                        <a
                            tabindex="0"
                            role="button"
                            data-toggle="popover"
                            class="no-decoration popover_focus"
                            data-trigger="focus"
                            aria-label="Tooltip majorité"
                            title="Proximité avec la majorité gouvernementale"
                            data-content="Le <b>taux de proximité avec la majorité gouvernementale</b> représente le pourcentage de fois où un député vote la même chose que le groupe présidentiel (La République en Marche).<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>."
                            id="popover_focus">
                            <?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?>
                        </a>
                    </h3>
                </div>
            </div>
            <div class="row">
                <?php if ($no_majorite) : ?>
                    <div class="col-12 mt-2">
                        <p>Cette statistique sera disponible dès qu'un nombre suffisant de votes sera atteint pour <?= $title ?>.</p>
                    </div>
                <?php else : ?>
                    <div class="col-lg-3 offset-lg-1 mt-2">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="c100 p<?= $majorite['score'] ?> m-0">
                                <span><?= $majorite['score'] ?> %</span>
                                <div class="slice">
                                    <div class="bar"></div>
                                    <div class="fill"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 infos mt-4 mt-lg-2">
                        <div class="texte ml-md-3 pl-md-3 mt-md-0 mt-3">
                            <p>
                                <?= $title ?> a voté comme la majorité gouvernementale dans <?= $majorite['score'] ?>% des cas. Les votes de <?= $title ?> sont comparés à ceux du groupe politique le plus gros de la majorité (<a href="<?= base_url() ?>groupes/legislature-<?= $groupMajority['legislature'] ?>/<?= mb_strtolower($groupMajority['libelleAbrev']) ?>"><?= name_group($groupMajority['libelle']) ?></a>).
                            </p>
                            <p>
                                <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite['all'] ?></b> de la majorité gouvernementale que la moyenne des députés non membres de la majorité (<?= $majorite['all'] ?>%).
                            </p>
                            <?php if ($majorite['group']): ?>
                                <p>
                                    De plus, <?= $title ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite['group'] ?></b> de la majorité gouvernementale que la moyenne des députés de son groupe politique (<?= $majorite['group'] ?>%).
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?> <!-- END CARD MAJORITE -->