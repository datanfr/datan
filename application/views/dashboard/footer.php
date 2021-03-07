<!-- Main Footer -->
<footer class="main-footer">
  <!-- To the right -->
  <div class="float-right d-none d-sm-inline">
    ***
  </div>
  <!-- Default to the left -->
  <strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
</footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="<?= asset_url() ?>js/dashboard/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?= asset_url()?>js/dashboard/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="<?= asset_url() ?>js/dashboard/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<!-- <script src="plugins/chart.js/Chart.min.js"></script> -->
<!-- Sparkline -->
<!-- <script src="plugins/sparklines/sparkline.js"></script> -->
<!-- JQVMap -->
<!-- <script src="plugins/jqvmap/jquery.vmap.min.js"></script> -->
<!-- <script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script> -->
<!-- jQuery Knob Chart -->
<!-- <script src="plugins/jquery-knob/jquery.knob.min.js"></script> -->
<!-- daterangepicker -->
<!-- <script src="plugins/moment/moment.min.js"></script> -->
<!-- <script src="plugins/daterangepicker/daterangepicker.js"></script> -->
<!-- Tempusdominus Bootstrap 4 -->
<!-- <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script> -->
<!-- Summernote -->
<!-- <script src="<?= asset_url() ?>js/dashboard/summernote-bs4.min.js"></script> -->
<!-- overlayScrollbars -->
<!-- <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script> -->
<!-- AdminLTE App -->
<script src="<?= asset_url() ?>js/dashboard/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!-- <script src="dist/js/pages/dashboard.js"></script> -->
<!-- AdminLTE for demo purposes -->
<!-- <script src="dist/js/demo.js"></script> -->

<!-- DataTables -->
<script src="<?= asset_url() ?>js/dashboard/jquery.dataTables.js"></script>
<script src="<?= asset_url() ?>js/dashboard/dataTables.bootstrap4.js"></script>
<script src="<?= asset_url() ?>js/dashboard/dataTables.fixedHeader.min.js"></script>
<script src="<?= asset_url() ?>js/dashboard/dataTables.buttons.min.js"></script>
<script src="<?= asset_url() ?>js/dashboard/buttons.colVis.min.js"></script>


<script>
  $(document).ready(function() {

    $('#table_votes_datan').dataTable({
      "order": [[0, "desc"]]
    });

    $('#table_votes_an').DataTable( {
        dom: 'Bfrtip',
        //"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        fixedHeader: true,
        "paging": true,
        "order": [[ 1, "desc" ]],
        buttons: [
          {
            extend: 'colvisGroup',
            text: 'Montrer infos votes',
            show: [0, 1, 2, 3, 4, 5, 6, 7],
            hide: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]
          },
          {
            extend: 'colvisGroup',
            text: 'Montrer groupes',
            show: [0, 1, 2, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
            hide: [3, 4, 5, 6, 7]
          },
          {
            extend: 'colvisGroup',
            text: 'MONTRER TOUT',
            show: ':hidden'
          }
        ]
    } );

    $('#table_analyses').DataTable({
      "order": [[0, "desc"]]
    });

      for(let link of $('.nav-treeview .nav-link')){
        let searchParams = new URLSearchParams(window.location.search);
        console.log(searchParams.get('group'));
        if($(link).attr('href').endsWith(location.pathname) 
        || $(link).attr('href').endsWith('class_loyaute_group?group=' +searchParams.get('group'))){
          $(link).addClass('active');
          $(link).closest('.has-treeview').addClass('menu-open');
          $(link).closest('.has-treeview').addClass('active');
        }
      }

} );

</script>


</body>
</html>
