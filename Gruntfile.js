module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// Metadata
		meta: {
			pluginPath: 'plg_zlframework/',
			buildPath: 'build/'
		},

		// copy Plugin folder to Build folder
		copy: {
			main: {
				files: [
					{expand: true, cwd: '<%= meta.pluginPath %>', src: ['**'], dest: '<%= meta.buildPath %>'} // makes all src relative to cwd
				]
			},

			// save JS scripts as dev uncompressed versions
			dev: {
				files: [
					{
						expand: true, 
						cwd: '<%= meta.buildPath %>', 
						src: [
							'**/zlframework/zlux/**/*.js', // all ZLUX plugins
							'!**/*zlux/assets/**/*.js' // discart assets
						],
						dest: '<%= meta.buildPath %>',
						ext: '.dev.js'
					}
				]
			}
		},

		// JSHint
		jshint: {
			// configure JSHint (documented at http://www.jshint.com/docs/)
			options: {
				// more options here if you want to override JSHint defaults
				globals: {
					jQuery: true
				}
			},
			// define the files to lint
			files: ['<%= meta.pluginPath %>zlframework/zlux/zluxMain.js'],
		},
				
		// compress the JS files
		uglify: {
			options: {
				banner: '/* ===================================================\n' +
						' * <%= pkg.plugin.name %>\n' +
						' * <%= pkg.plugin.link %>\n' +
						' * ===================================================\n' +
						' * Copyright (C) JOOlanders SL\n' +
						' * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only\n' +
						' * ========================================================== */\n',
			},
			dist: {
				files: [
					{
						expand: true, 
						cwd: '<%= meta.buildPath %>', 
						src: [
							'**/zlframework/zlux/**/*.js', // all ZLUX plugins
							'!**/*zlux/assets/**/*.js', // discart assets
							'!**/*.dev.js' // discart dev versions
						],
						dest: '<%= meta.buildPath %>'
					}
				]
			}
		},

		// replacer
		replacer: {

			// main XML
			xml: {
				options: {
					replace: {
						'<!-- VERSION -->' : '<%= pkg.plugin.version %>'
					}
				},
				src: 'plg_zlframework/zlframework.xml',
				dest: 'build/zlframework.xml'
			}
		},

		// make a zipfile
		compress: {
			main: {
				options: {
					archive: 'plg_zlframework.zip', // .tar.gz, .zip
					mode: 'zip' // tgz, zip
				},
				files: [
					{expand: true, cwd: '<%= meta.buildPath %>', src: ['**'], dest: '/'} // makes all src relative to cwd
				]
			}
		},

		// remove temporal build files
		clean: ['<%= meta.buildPath %>']
	});

	// load in Grunt plugins
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-replacer');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-clean');

	// register tasks
	grunt.registerTask('default', ['copy', 'uglify', 'compress', 'clean']);

	grunt.registerTask('test', ['replacer']);
	grunt.registerTask('min', ['uglify']);

};