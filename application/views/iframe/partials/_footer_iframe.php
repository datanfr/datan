</div>
    </main>
    <!-- JS files -->
    <script src="<?= asset_url(); ?>js/libraries/jquery/jquery-3.5.1.min.js"></script>
    <script src="<?= asset_url(); ?>js/libraries/jquery/jquery-ui.min.js"></script>
    <script src="<?= asset_url(); ?>js/libraries/popper/popper.min.js"></script>
    <script type="text/javascript" src="<?= asset_url() ?>js/libraries/bootstrap/bootstrap.min.js"></script>
    <?php if (isset($js_to_load_before_datan)) : ?>
      <?php foreach ($js_to_load_before_datan as $file) : ?>
        <script src="<?= asset_url(); ?>js/<?= $file ?>.js?v=<?= get_version() ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>
    <script type="text/javascript" src="<?= asset_url() ?>js/libraries/jquery/jquery.unveil.min.js"></script>

    <?php if (isset($js_to_load)) : ?>
      <?php foreach ($js_to_load as $file) : ?>
        <script type="text/javascript" src="<?= asset_url() ?>js/<?= $file ?>.js?v=<?= get_version() ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>

    <script type="text/javascript" src="<?= asset_url() ?>js/main.min.js?v=<?= get_version() ?>"></script>
    <script type="text/javascript" src="<?= asset_url() ?>js/datan/url_obf2.min.js"></script>

    </body>

    </html>
