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
    // babel the main.js
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
    // uglify the js 
    uglify: {
      options: {
        compress: true,
        sourceMap : true,
        sourceMapName : 'assets/js/main.map'
      },
      datatables: {
        src: [
          'assets/js/datatable/jquery.dataTables.min.js',
          'assets/js/datatable/dataTables.bootstrap4.min.js',
          'assets/js/datatable/dataTables.responsive.min.js',
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
    critical: {
      // Homepage
      index: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 1200,
          height: 1350
        },
        // The source file
        src: 'http://localhost/datan/',
        // The destination file
        dest: 'assets/css/critical/index.css'
      },
      // Homepage mobile
      indexMobile: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 375,
          height: 1800
        },
        // The source file
        src: 'http://localhost/datan/',
        // The destination file
        dest: 'assets/css/critical/index-mobile.css'
      },
      // Page city
      city: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 1200,
          height: 1350
        },
        // The source file
        src: 'http://localhost/datan/deputes/ille-et-vilaine-35/ville_rennes',
        // The destination file
        dest: 'assets/css/critical/city.css'
      },
      // Page city mobile
      cityMobile: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 375,
          height: 812
        },
        // The source file
        src: 'http://localhost/datan/deputes/ille-et-vilaine-35/ville_rennes',
        // The destination file
        dest: 'assets/css/critical/city-mobile.css'
      },
      // Page depute
      depute_individual: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 1200,
          height: 1900
        },
        // The source file
        src: 'http://localhost/datan/deputes/reunion-974/depute_karine-lebon',
        // The destination file
        dest: 'assets/css/critical/depute_individual.css'
      },
      // Page depute Mobile
      depute_individualMobile: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 375,
          height: 3200
        },
        // The source file
        src: 'http://localhost/datan/deputes/reunion-974/depute_karine-lebon',
        // The destination file
        dest: 'assets/css/critical/depute_individual-mobile.css'
      },
      // Page groupe
      groupe_individual: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 1200,
          height: 1350
        },
        // The source file
        src: 'http://localhost/datan/groupes/larem',
        // The destination file
        dest: 'assets/css/critical/groupe_individual.css'
      },
      // Page groupe Mobile
      groupe_individualMobile: {
        options: {
          base: './',
          css: [
              'assets/css/main.css'
          ],
          width: 375,
          height: 1700
        },
        // The source file
        src: 'http://localhost/datan/groupes/larem',
        // The destination file
        dest: 'assets/css/critical/groupe_individual-mobile.css'
      },
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
  grunt.registerTask('default', ['sass', 'purifycss', 'concat', 'babel', 'uglify', 'critical']);

};
