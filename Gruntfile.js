module.exports = function(grunt) {

  var date = new Date();
  var today = date.getFullYear() + '' + (date.getMonth()+1) + '' + date.getDate();


  grunt.initConfig({
    purifycss: {
      options: {
        minify: true
      },
      target: {
        src: ['application/views/*/*.php'],
        css: ['assets/css/main.css'],
        dest: 'assets/css/main_' + today + '.css'
      }
    },
    critical: {
      // Homepage
      index: {
        options: {
          base: './',
          width: 1200,
          height: 1500
        },
        // The source file
        src: 'http://localhost/datan/',
        // The destination file
        dest: 'assets/css/critical/index.css'
      },
      // Page city
      city: {
        options: {
          base: './',
          width: 1200,
          height: 1500
        },
        // The source file
        src: 'http://localhost/datan/deputes/ille-et-vilaine-35/ville_rennes',
        // The destination file
        dest: 'assets/css/critical/city.css'
      },
      // Page depute
      depute_individual: {
        options: {
          base: './',
          width: 1200,
          height: 1500
        },
        // The source file
        src: 'http://localhost/datan/deputes/lozere-48/depute_pierre-morelalhuissier',
        // The destination file
        dest: 'assets/css/critical/depute_individual.css'
      },
      // Page groupe
      groupe_individual: {
        options: {
          base: './',
          width: 1200,
          height: 1500
        },
        // The source file
        src: 'http://localhost/datan/groupes/larem',
        // The destination file
        dest: 'assets/css/critical/groupe_individual.css'
      },
      },
      uglify: {
        options: {
          compress: true
        },
        datatables: {
          src: [
            'assets/js/datatable/jquery.dataTables.min.js',
            'assets/js/datatable/dataTables.bootstrap4.min.js',
            'assets/js/datatable/dataTables.responsive.min',
            'assets/js/datatable/data-table-datan.js'
          ],
          dest: 'assets/js/' + today + '_datatable-datan.min.js'
        }
      }
    });

    // Load the plugins
    grunt.loadNpmTasks('grunt-critical');
    grunt.loadNpmTasks('grunt-purifycss');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Default tasks.
    grunt.registerTask('default', ['purifycss', 'critical', 'uglify']);

};
