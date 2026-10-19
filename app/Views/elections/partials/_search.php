<div class="container-fluid pg-elections-candidats search py-5 bg-info">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="city-search-block">
                    <h2 class="mb-1">Candidats et résultats dans votre commune</h2>
                    <p class="text-muted mb-3">Accédez aux listes candidates et aux résultats dans votre commune</p>
                    <div id="city-search-bloc" class="search-bloc">
                        <form autocomplete="off" action="recherche.php" id="searchForm" method="GET">
                            <div class="search">
                                <div class="search_icon">
                                    <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-search.svg") ?>
                                </div>
                                <input id="citySearch" type="text" class="randomized" placeholder="Nom de commune (ex : Paris, Lyon, Marseille)">
                                <div class="search-results" id="search-results-bloc" style="display: none">
                                    <div id="search-results-list"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- TOP 10 CITIES -->
                <div class="mt-5">
                    <p class="top-cities-label text-center">Grandes villes</p>
                    <div class="d-flex justify-content-center flex-wrap">
                        <?php foreach(array_slice($communes, 0, 15) as $commune): ?>
                            <a href="<?= base_url() ?>elections/resultats/<?= $commune['slug'] ?>/ville_<?= $commune['commune_slug'] ?>" class="top-city-pill"><?= $commune['commune_nom'] ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>