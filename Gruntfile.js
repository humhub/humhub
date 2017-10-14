module.exports = function (grunt) {

    

    var uglifyAssetcfg = {};
    uglifyAssetcfg[grunt.option('to')] = grunt.option('from');
    
    var cssMinAssetcfg = {};
    cssMinAssetcfg[grunt.option('to')] = [grunt.option('from')];
    
    grunt.log.write(grunt.option('from'));
    
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: ["assets/*"],
        shell: {
            buildAssets: {
                command: "rm static/js/all-*.js ; rm static/css/all-*.css ; rm -rf static/assets/* ; cd protected ; php yii asset humhub/config/assets.php humhub/config/assets-prod.php"
            },
            buildSearch: {
                command: "cd protected ; php yii search/rebuild"
            },
            buildTheme: {
                command: function(name) {
                    var theme = name || grunt.option('name') || "HumHub";
                    return "cd themes/"+theme+"/less ; lessc -x build.less ../css/theme.css";
                }
            },
            migrateCreate: {
                command: function(name) {
                    var migrationName = name || grunt.option('name');
                    return "cd protected; php yii migrate/create "+migrationName;
                }
            },
            migrateUp: {
                command: function(modules) {
                    var includeModuleMigrations = modules || grunt.option('modules') || "1";
                    return "cd protected; php yii migrate/up --includeModuleMigrations="+includeModuleMigrations;
                }
            }
            
        },
        uglify: {
            build: {
                files: {
                    'js/dist/humhub.all.min.js': ['js/dist/humhub.all.js']
                }
            },
            assets: {
                options: {
                    preserveComments: /^!|@preserve|@license|@cc_on/i
                },
                files: uglifyAssetcfg
            }
        },
        cssmin: {
            target: {
                files: cssMinAssetcfg
            }
        },
        less: {
            dev: {
                files: {
                    'themes/HumHub/css/less/theme.css': 'themes/HumHub/css/less/theme.less'
                }
            }
        }
    });
    
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-shell');
    
    //grunt.registerTask('default', ['watch']);
    grunt.registerTask('build-assets', ['shell:buildAssets']);
    grunt.registerTask('build-search', ['shell:buildSearch']);

    grunt.registerTask('migrate-up', ['shell:migrateUp']);

    /**
     * Will create a new migration into the protected/humhub/migrations directory
     *
     * > grunt migrate-create --name=MyMigration
     */
    grunt.registerTask('migrate-create', ['shell:migrateCreate']);
    
    /**
     * Build default HumHub theme:
     * 
     * > grunt build-theme
     * 
     * Build named theme:
     * > grunt build-theme --name=MyTheme
     * 
     * or
     * 
     * > grunt shell:buildTheme:MyTheme
     */
    grunt.registerTask('build-theme', ['shell:buildTheme']);
};