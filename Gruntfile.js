module.exports = function (grunt) {

    grunt.initConfig({
        concat: {
            options: {
                separator: ';',
            },
            dist: {
                src: ['js/humhub.core.js', 'js/humhub.util.js', 'js/humhub.additions.js',
                    'js/humhub.client.js', 'js/humhub.ui.js', 'js/humhub.ui.modal.js', 'js/humhub.actions.js',
                    'js/humhub.content.js', 'js/humhub.stream.js'],
                dest: 'js/dist/humhub.all.js'
            }
        },
        watch: {
            js: {
                files: ['js/*.js'],
                tasks: ['build']
            }
        },
        clean: ["assets/*"],
        uglify: {
            build: {
                files: {
                    'js/dist/humhub.all.min.js': ['js/dist/humhub.all.js']
                }
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
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');

    grunt.registerTask('default', ['watch']);
    grunt.registerTask('build', ['concat', 'uglify', 'clean']);
};