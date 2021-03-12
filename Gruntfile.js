module.exports = function (grunt) {

  var date = new Date();
  var today = date.getFullYear() + '' + (date.getMonth() + 1) + '' + date.getDate();


  grunt.initConfig({
    // First compile main.scss to assets/css/main.css and bootstrap.scss to assets/css/bootstrap.css
    sass: {
      dist: {
        options: {
          style: 'compressed',
          compass: false,
          sourcemap: false
        },
        files: {
          'assets/css/main.css': [
            'main.scss'
          ],
          'assets/css/bootstrap.css': [
            'bootstrap.scss'
          ]
        }
      }
    },
    // Purify only bootstrap
    purifycss: {
      options: {
        minify: true
      },
      target: {
        src: ['application/views/*/*.php'],
        css: ['assets/css/bootstrap.css'],
        dest: 'assets/css/bootstrap.css'
      }
    },
    // Concatenate bootstrap and main together to assets/css/main.css
    concat:{
      css:{
        src: [
          'assets/css/bootstrap.css',
          'assets/css/main.css'
        ],
        dest: 'assets/css/main.css'
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
    babel: {
      options: {
        sourceMap: true,
        presets: ["@babel/preset-env"],
      },
      dist: {
        files: {
          "assets/js/main-es5.js": "assets/js/main.js",
        },
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
        dest: 'assets/js/datatable-datan.min.js'
      },
      main: {
        src: [
          'assets/js/main-es5.js'
        ],
        dest: 'assets/js/main.min.js'
      }
    },
    watch: {
      css: {
        files: 'main.scss',
        tasks: ['sass','purifycss', 'concat',],
        options: {
          livereload: true,
        },
      },
      scripts: {
        files: ['assets/js/main.js'],
        tasks: ['babel', 'uglify'],
        options: {
          spawn: false,
        },

      }
    },
  });

  // Load the plugins
  grunt.loadNpmTasks('grunt-critical');
  grunt.loadNpmTasks('grunt-purifycss');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-babel');

  // Default tasks. Mind the order
  grunt.registerTask('default', ['sass', 'purifycss', 'concat', 'critical', 'babel', 'uglify']);

};
