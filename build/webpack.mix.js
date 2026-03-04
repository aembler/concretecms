/**
 * Import and configure laravel mix.
 */
let mix = require('laravel-mix');
const path = require('path');
const tailwindcss = require('@tailwindcss/postcss')
const fs = require('fs');

mix.override((config) => {
    delete config.watchOptions;
});

mix.webpackConfig({
    /* cache: false,*/ // Uncomment if you're working with changes in node_modules like developing bedrock
    snapshot: {
        // Don't treat node_modules as immutable when developing portal-linked local packages.
        managedPaths: []
    },
    watchOptions: {
        followSymlinks: true,
        // Ignore most node_modules, but keep watching @concretecms/backendui.
        ignored: /node_modules[\\/](?!@concretecms[\\/]backendui)/
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                loader: "ts-loader",
                options: {
                    appendTsSuffixTo: [/\.vue$/],

                    // https://laracasts.com/discuss/channels/elixir/wabpack-cli-error-on-reload-watch
                    transpileOnly: true,
                },
                exclude: /node_modules/
            }
        ]
    },
    resolve: {
        symlinks: false,
        extensions: ["*", ".js", ".jsx", ".vue", ".ts", ".tsx"]
    },
    externals: {
        jquery: 'jQuery',
        bootstrap: true,
        vue: 'Vue',
        moment: 'moment'
    }
});

mix.options({
    processCssUrls: false
});


/********************************************************/
/* IMPORTANT: when you add/remove a generated asset,    */
/* remember to update libraries/git-skip.js accordingly */
/********************************************************/

/**
 * Copy pre-minified assets.
 */
if (mix.inProduction()) {
    mix.copy('node_modules/vue/dist/vue.global.prod.js', '../concrete/js/vue.js');
    mix.copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', '../concrete/js/bootstrap.js');
    mix.copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map', '../concrete/js/bootstrap.bundle.min.js.map');
    // Moment JS
    mix.copy('node_modules/moment/min/moment.min.js', '../concrete/js/moment.js');
    mix.copy('node_modules/moment/min/moment.min.js.map', '../concrete/js/moment.min.js.map');

    // Ckeditor
    mix.copy('node_modules/ckeditor4/adapters', '../concrete/js/ckeditor/adapters');
    mix.copy('node_modules/ckeditor4/ckeditor.js', '../concrete/js/ckeditor/ckeditor.js');
    mix.copy('node_modules/ckeditor4/config.js', '../concrete/js/ckeditor/config.js');
    mix.copy('node_modules/ckeditor4/contents.css', '../concrete/js/ckeditor/contents.css');
    mix.copy('node_modules/ckeditor4/lang', '../concrete/js/ckeditor/lang');
    mix.copy('node_modules/ckeditor4/plugins', '../concrete/js/ckeditor/plugins');
    mix.copy('node_modules/ckeditor4/skins', '../concrete/js/ckeditor/skins');
    mix.copy('node_modules/ckeditor4/styles.js', '../concrete/js/ckeditor/styles.js');
    mix.copy('node_modules/ckeditor4/vendor', '../concrete/js/ckeditor/vendor');

} else {
    mix.copy('node_modules/vue/dist/vue.global.js', '../concrete/js/vue.js');
}

// Now let's proceed with public entrypoints
mix.setPublicPath('../concrete');

// Core Concrete Theme for Install and Other Things
mix
    .sass('assets/themes/concrete/scss/main.scss', 'themes/concrete', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    .js('assets/themes/concrete/js/main.js', 'themes/concrete').vue()

// Installer
mix
    .sass('assets/installer/scss/installer.scss', 'css/installer.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    .js('assets/installer/js/installer.js', 'js/installer.js').vue()

// BackendUI (CMS and Dashboard)
mix.postCss('assets/backendui.css', 'css/backendui.css', [
    tailwindcss(),
    require('autoprefixer'),
])
.version()

// CMS
mix
    .sass('assets/cms.scss', 'css/cms.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
mix
    .js('assets/cms.js', 'concrete/js/cms.js')
    .vue({version: 3, customElement: true})

// Atomik Theme
mix
    .sass('../concrete/themes/atomik/css/presets/default/main.scss', 'themes/atomik/css/skins/default.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    // currently commenting these out just to make the build process faster while we're implementing novaui
    /*
    .sass('../concrete/themes/atomik/css/presets/rustic-elegance/main.scss', 'themes/atomik/css/skins/rustic-elegance.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    .sass('../concrete/themes/atomik/css/presets/coastal-breeze/main.scss', 'themes/atomik/css/skins/coastal-breeze.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    .sass('../concrete/themes/atomik/css/presets/golden-meadow/main.scss', 'themes/atomik/css/skins/golden-meadow.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    .sass('../concrete/themes/atomik/css/presets/misty-sage/main.scss', 'themes/atomik/css/skins/misty-sage.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    .sass('../concrete/themes/atomik/css/presets/amber-twilight/main.scss', 'themes/atomik/css/skins/amber-twilight.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })
    .sass('../concrete/themes/atomik/css/presets/midnight-velvet/main.scss', 'themes/atomik/css/skins/midnight-velvet.css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/')
            ]
        }
    })*/
    .js('assets/themes/atomik/js/main.js', 'themes/atomik').vue()

// Turn off notifications
mix
    .disableNotifications()
    .options({
        clearConsole: false,
        // Disable extracting licenses from comments
        terser: {
            extractComments: false,
        }
    })
