/**************************************************
 * Build with grunt.
 *
 * @description automate the build process for wordpress plugin
 * @version 1.0.0
 * @author pavenest
 *
 **************************************************/

const scriptVersion = '1.0.0';

/**
 *
 * Grunt Configuration
 *
 */
module.exports = function (grunt) {
    'use strict';

    const projectTextDomain = 'am-test';
    const srcDir = './';
    const distDir = '../dist/' + projectTextDomain;


    const scssSource = [
        {
            cwd: './resources/scss/',
            src: ['*.scss', '!_*.scss'],
            dest: './assets/css',
        }
    ];



    const jsSource = {
        js: [
            {
                cwd: 'dev/js/',
                src: ['app.js'],
                dest: 'assets/js/',
            },
        ],
    };

    require('load-grunt-tasks')(grunt);

    // Grunt task begins
    grunt.initConfig({

        sass: {
            dist: {
                options: {
                    style: 'expanded',
                    trace: true,
                    sourceMap:false
                },
                files: {
                   './assets/css/app.css':'./resources/scss/app.scss'
                },
                files3: scssSource.map(value => ({
                    expand: true,
                    ext: '.css',
                    cwd: value.cwd,
                    src: value.src,
                    dest: value.dest,
                }))
            },
        },


        addtextdomain: {
            options: {
                updateDomains: true,
            },
            target: {
                files: {
                    src: [
                        '*.php',
                        '**/*.php',
                        '!node_modules/**',
                        '!tests/**',
                        '!dev/**'
                    ]
                }
            }
        },

        checktextdomain: {
            standard: {
                options: {
                    text_domain: projectTextDomain,
                    keywords: [
                        '__:1,2d',
                        '_e:1,2d',
                        '_x:1,2c,3d',
                        'esc_html__:1,2d',
                        'esc_html_e:1,2d',
                        'esc_html_x:1,2c,3d',
                        'esc_attr__:1,2d',
                        'esc_attr_e:1,2d',
                        'esc_attr_x:1,2c,3d',
                        '_ex:1,2c,3d',
                        '_n:1,2,4d',
                        '_nx:1,2,4c,5d',
                        '_n_noop:1,2,3d',
                        '_nx_noop:1,2,3c,4d',
                    ],
                },
                files: [{
                    src: [
                        srcDir + '**/*.php',
                        '!' + srcDir + 'node_modules/**',
                    ], //all php
                    expand: true,
                }],
            },
        },

        makepot: {
            target: {
                options: {
                    cwd: srcDir, // Directory of files to internationalize.
                    mainFile: '', // Main project file.
                    type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
                    updateTimestamp: false, // Whether the POT-Creation-Date should be updated without other changes.
                    updatePoFiles: false, // Whether to update PO files in the same directory as the POT file.
                },
            },
        },

        cssmin: {
            options: {
                force: true,
                compress: true,
                sourcemaps: false,
            },
            minify: {
                files: [{
                    expand: true,
                    src: [distDir + '**/*.css'],
                    dest: '',
                }],
            },
        },


        // Minify all .js files.
        terser: {
            options: {
                ie8: true,
                parse: {
                    strict: false,
                },
            },
            js: {
                files: [{
                    expand: true,
                    src: [distDir + '**/*.js'],
                    dest: '',
                }],
            },
        },


        // Sass linting with Stylelint.
        stylelint: {
            options: {
                fix: true,
                configFile: '.stylelintrc',
            },
            default: [srcDir + '**/*.scss'],
        },

        // All logging configuration
        log: {
            // before build starts log
            begin: `
───────────────────────────────────────────────────────────────────
# Project: ${projectTextDomain}
# Dist: ${distDir}
# Script Version: ${scriptVersion}
───────────────────────────────────────────────────────────────────
			`.cyan,

            // before textdomain tasks starts log
            tdmchecking: '\n>>'.green + ` Checking textdomain [${projectTextDomain}].`,
            csscomp: '\n>>'.yellow + ` Compiling CSS.`,

            // After finishing all tasks
            finish: `
╭─────────────────────────────────────────────────────────────────╮
│                                                                 │
│                      All tasks completed.                       │
│                        ~ Pavenest ~                             │
│                                                                 │
╰─────────────────────────────────────────────────────────────────╯
			`.green,
        },
    });


    grunt.loadNpmTasks('grunt-checktextdomain');
    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-contrib-sass');

    /* ---------------------------------------- *
     *  Registering TASKS
     * ---------------------------------------- */

    grunt.registerMultiTask('log', function () {
        grunt.log.writeln(this.data);
    });

    /**
     * NPM packages - grunt-checktextdomain, grunt-wp-i18n
     *
     */
    grunt.registerTask('add-tdm', ['addtextdomain']);
    grunt.registerTask('chk-tdm', ['checktextdomain']);
    grunt.registerTask('fix-tdm', ['log:begin', 'log:tdmchecking', 'addtextdomain', 'checktextdomain', 'log:finish']);
    grunt.registerTask('tdm-with-pot', ['log:begin', 'log:tdmchecking', 'addtextdomain', 'checktextdomain', 'makepot', 'log:finish']);


    grunt.registerTask('compile-css', ['log:begin', 'log:csscomp', 'sass', 'log:finish']);
    grunt.registerTask('default', ['sass']);

    /*
    grunt.registerTask('build', [
        'log:begin',
        (projectConfig.ignoreLint ? 'log:nolintwarning' : 'lint'),
        'fixtextdomain',
        'makepot',
        'boot',
        'minify',
        'compress',
        'log:finish',
    ]);
     */
};
