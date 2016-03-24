module.exports = function (grunt) {
    
    grunt.initConfig({
        concat: {
          options: {
            separator: ';',
          },
          dist: {
            src: ['js/humhub.init.js', 'js/humhub.util.js','js/humhub.scripts.js' ,'js/humhub.additions.js', 
                'js/humhub.client.js', 'js/humhub.ui.js', 'js/humhub.modules.js', 'js/humhub.content.js', 
                'js/humhub.stream.js', 'js/humhub.ui.modal.js'],
            dest: 'js/dist/humhub.all.js'
          }
        },
        watch: {
            js: {
                files: ['js/*.js'],
                tasks: ['build']
            }
        },
        clean: ["assets/*"]
    });
    
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');
    
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('build', ['concat', 'clean']);
}