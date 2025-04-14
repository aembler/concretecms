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
    resolve: {
        symlinks: false
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

mix.setPublicPath('../concrete');

/********************************************************/
/* IMPORTANT: when you add/remove a generated asset,    */
/* remember to update libraries/git-skip.js accordingly */
/********************************************************/

/**
 * Copy pre-minified assets.
 */
if (mix.inProduction()) {
    mix.copy('node_modules/jquery/dist/jquery.min.js', '../concrete/js/jquery.js');
    mix.copy('node_modules/vue/dist/vue.global.prod.js', '../concrete/js/vue.js');
} else {
    mix.copy('node_modules/vue/dist/vue.global.js', '../concrete/js/vue.js');
}

/* Core Concrete Theme for Install and Other Things */
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

    mix.postCss('assets/backendui.css', 'css/backendui.css', [
        tailwindcss(),
        require('autoprefixer'),
    ])
    .version()

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
