module.exports = function (grunt) {

    var uglifyAssetcfg = {};
    uglifyAssetcfg[grunt.option('to')] = grunt.option('from');
    
    var cssMinAssetcfg = {};
    cssMinAssetcfg[grunt.option('to')] = [grunt.option('from')];

    var isWin = function() {
        return (process.platform === "win32");
    };

    var cmdSep = function() {
        return isWin() ? '&' : ';';
    };

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: ["assets/*"],
        shell: {
            buildAssets: {
                command: function() {
                    let rm = isWin() ? 'del' : 'rm';
                    let sep = cmdSep();
                    let delAssets = isWin() ? '(For /D %i in (static\\assets\\*.*) do (rmdir %i /S /Q))' : `${rm} -rf static/assets/*/`;
                    let dirSep = isWin() ? "\\" : '/';
                    let jsFile = `static${dirSep}js${dirSep}all-*.js`;
                    let cssFile = `static${dirSep}css${dirSep}all-*.css`;
                    return `${rm} ${jsFile} ${sep} ${rm} ${cssFile} ${sep} ${delAssets} ${sep} cd protected ${sep} php yii asset humhub/config/assets.php humhub/config/assets-prod.php`;
                }
            },
            buildSearch: {
                command: function() {
                    let sep = cmdSep();
                    return `cd protected ${sep} php yii search/rebuild`;
                }
            },
            testServer: {
                command: "php -S localhost:8080"
            },
            testRun: {
                command: function() {
                    let sep = cmdSep();
                    let moduleName = grunt.option('module') || grunt.option('m') ||  null;
                    let doBuild = grunt.option('build') || false;
                    let base = process.cwd();

                    let codeceptPath = `${base}/protected/vendor/codeception/codeception/codecept`;
                    let rootTestPath = `${base}/protected/humhub/tests`;

                    let testPath = rootTestPath;
                    if(moduleName) {
                        testPath = `${base}/protected/humhub/modules/${moduleName}/tests`;
                    }

                    let suite = grunt.option('suite') || null;
                    let path = grunt.option('path') || null;
                    let executionPath = '';

                    if(suite) {
                        executionPath = suite;
                    } else if(path) {
                        if(path.indexOf('codeception') !== 0) {
                            path = 'codeception' + ((path.indexOf('/') !== 0) ? '/' : '') + path;
                        }
                        executionPath = path;
                    }

                    let options = grunt.option('options') || '';
                    options += grunt.option('raw') ? ' --no-ansi' : '';
                    options += grunt.option('env') ? ' --env '+ grunt.option('env') : '';


                    let build =  `cd ${rootTestPath} ${sep} php ${codeceptPath} build`;

                    let run = `cd ${testPath} ${sep} php ${codeceptPath} run ${executionPath} ${options}`;

                    return doBuild ? `${build} ${sep} ${run}` : run;
                }
            },
            buildTheme: {
                command: function(name) {
                    let theme = name || grunt.option('name') || "HumHub";
                    let sep = cmdSep();
                    return `cd themes/${theme}/less ${sep} lessc -x build.less ../css/theme.css`;
                }
            },
            migrateCreate: {
                command: function(name) {
                    let migrationName = name || grunt.option('name');
                    let sep = cmdSep();
                    return `cd protected ${sep} php yii migrate/create ${migrationName}`;
                }
            },
            migrateUp: {
                command: function(modules) {
                    let includeModuleMigrations = modules || grunt.option('modules') || "1";
                    let sep = cmdSep();
                    return `cd protected ${sep} php yii migrate/up --includeModuleMigrations=${includeModuleMigrations}`;
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
    grunt.registerTask('test-server', ['shell:testServer']);
    grunt.registerTask('test', ['shell:testRun']);
};