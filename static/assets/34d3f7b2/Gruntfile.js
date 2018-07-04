'use strict';

module.exports = function(grunt) {

	// Get devDependencies
	require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

	// Displays the execution time of grunt tasks
	require('time-grunt')(grunt);

	// Config
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// uglify
		uglify: {
			options: {
				sourceMap: true,
				compress: {
					drop_console: true,
					drop_debugger: true
				},
				banner: '/* <%= pkg.title %> - v<%= pkg.version %>\n' +
						' * Copyright (c)<%= grunt.template.today("yyyy") %> Mathias Bynens\n' +
						' * <%= grunt.template.today("yyyy-mm-dd") %>\n' +
						' */'
			},
			minify : {
				files: {
					'jquery.placeholder.min.js': ['jquery.placeholder.js']
				}
			}
		}

	});

	/**
	 * Register own tasks by putting together existing ones
	 */

	// Default task
	grunt.registerTask('default',
		['uglify']
	);

};
