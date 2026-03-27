import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const backenduiRoot = fs.realpathSync(path.resolve(__dirname, 'node_modules/@concretecms/backendui'))
const bedrockRoot = fs.realpathSync(path.resolve(__dirname, 'node_modules/@concretecms/bedrock'))
const vueShimPath = path.resolve(__dirname, 'vite/shims/vue-legacy-default.js')
const hotFilePath = path.resolve(__dirname, 'hot')
const reportSassDeprecations = process.env.SASS_REPORT_DEPRECATIONS === '1'

function createScssOptions() {
    return {
        quietDeps: !reportSassDeprecations,
        silenceDeprecations: reportSassDeprecations
            ? []
            : [
                'legacy-js-api',
                'import',
                'global-builtin',
                'color-functions',
                'mixed-decls',
                'slash-div',
            ],
    }
}

function concreteHotFilePlugin() {
    return {
        name: 'concrete-hot-file',
        configureServer(server) {
            const writeHotFile = () => {
                const address = server.httpServer?.address()
                if (!address || typeof address === 'string') {
                    return
                }

                const host = server.config.server.host && server.config.server.host !== true
                    ? server.config.server.host
                    : 'localhost'
                const protocol = server.config.server.https ? 'https' : 'http'
                const url = `${protocol}://${host}:${address.port}`

                fs.writeFileSync(hotFilePath, url)
            }

            server.httpServer?.once('listening', writeHotFile)

            const cleanup = () => {
                if (fs.existsSync(hotFilePath)) {
                    fs.unlinkSync(hotFilePath)
                }
            }

            server.httpServer?.once('close', cleanup)
            process.once('exit', cleanup)
            process.once('SIGINT', () => {
                cleanup()
                process.exit(130)
            })
            process.once('SIGTERM', () => {
                cleanup()
                process.exit(143)
            })
        },
        closeBundle() {
            if (fs.existsSync(hotFilePath)) {
                fs.unlinkSync(hotFilePath)
            }
        },
    }
}

export default defineConfig(({ mode }) => ({
    root: __dirname,
    publicDir: false,
    base: '/build/dist/',
    define: {
        'process.env.NODE_ENV': JSON.stringify(mode),
        'process.env.LANG': JSON.stringify(process.env.LANG ?? ''),
    },
    plugins: [
        vue({
            customElement: /\.ce\.vue$/,
        }),
        concreteHotFilePlugin(),
    ],
    resolve: {
        preserveSymlinks: true,
        alias: {
            '@concretecms/backendui': backenduiRoot,
            '@concretecms/bedrock': bedrockRoot,
            vue: vueShimPath,
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        origin: 'http://127.0.0.1:5173',
        cors: true,
        fs: {
            allow: [
                __dirname,
                path.resolve(__dirname, '..'),
                backenduiRoot,
                bedrockRoot,
            ],
        },
    },
    build: {
        outDir: path.resolve(__dirname, 'dist'),
        emptyOutDir: true,
        manifest: true,
        sourcemap: mode !== 'production',
        rollupOptions: {
            input: {
                'assets/cms/js/cms.js': path.resolve(__dirname, 'assets/cms/js/cms.js'),
                'assets/cms/css/app.css': path.resolve(__dirname, 'assets/cms/css/app.css'),
                'assets/cms/css/page.css': path.resolve(__dirname, 'assets/cms/css/page.css'),
                'assets/installer/js/installer.js': path.resolve(__dirname, 'assets/installer/js/installer.js'),
                'assets/installer/css/installer.css': path.resolve(__dirname, 'assets/installer/css/installer.css'),
                'assets/themes/concrete/js/main.js': path.resolve(__dirname, 'assets/themes/concrete/js/main.js'),
                'assets/themes/concrete/scss/main.scss': path.resolve(__dirname, 'assets/themes/concrete/scss/main.scss'),
                'assets/themes/nova_dashboard/js/main.js': path.resolve(__dirname, 'assets/themes/nova_dashboard/js/main.js'),
                'assets/themes/nova_dashboard/css/main.css': path.resolve(__dirname, 'assets/themes/nova_dashboard/css/main.css'),
                'assets/themes/atomik/js/main.js': path.resolve(__dirname, 'assets/themes/atomik/js/main.js'),
                'concrete/themes/atomik/css/presets/default/main.scss': path.resolve(__dirname, '../concrete/themes/atomik/css/presets/default/main.scss'),
                'concrete/themes/atomik/css/presets/rustic-elegance/main.scss': path.resolve(__dirname, '../concrete/themes/atomik/css/presets/rustic-elegance/main.scss'),
                'concrete/themes/atomik/css/presets/coastal-breeze/main.scss': path.resolve(__dirname, '../concrete/themes/atomik/css/presets/coastal-breeze/main.scss'),
                'concrete/themes/atomik/css/presets/golden-meadow/main.scss': path.resolve(__dirname, '../concrete/themes/atomik/css/presets/golden-meadow/main.scss'),
                'concrete/themes/atomik/css/presets/misty-sage/main.scss': path.resolve(__dirname, '../concrete/themes/atomik/css/presets/misty-sage/main.scss'),
                'concrete/themes/atomik/css/presets/amber-twilight/main.scss': path.resolve(__dirname, '../concrete/themes/atomik/css/presets/amber-twilight/main.scss'),
                'concrete/themes/atomik/css/presets/midnight-velvet/main.scss': path.resolve(__dirname, '../concrete/themes/atomik/css/presets/midnight-velvet/main.scss'),
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: createScssOptions(),
        },
    },
}))
