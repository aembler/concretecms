import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

import vue from '@vitejs/plugin-vue'
import { build } from 'vite'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const concreteRoot = path.resolve(__dirname, '../concrete')
const viteTempRoot = path.resolve(__dirname, '.vite-temp')
const backenduiRoot = fs.realpathSync(path.resolve(__dirname, 'node_modules/@concretecms/backendui'))
const bedrockRoot = fs.realpathSync(path.resolve(__dirname, 'node_modules/@concretecms/bedrock'))
const vueShimPath = path.resolve(__dirname, 'vite/shims/vue-legacy-default.js')
const reportSassDeprecations = process.env.SASS_REPORT_DEPRECATIONS === '1'

const mode = getArgValue('--mode') ?? 'development'
const watch = process.argv.includes('--watch')
const production = mode === 'production'

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

const javascriptTargets = [
    {
        entry: path.resolve(__dirname, 'assets/installer/js/installer.js'),
        fileName: 'js/installer',
        globalName: 'ConcreteInstallerBuild',
    },
    {
        entry: path.resolve(__dirname, 'assets/cms/js/cms.js'),
        fileName: 'js/cms',
        globalName: 'ConcreteCmsBuild',
    },
    {
        entry: path.resolve(__dirname, 'assets/themes/concrete/js/main.js'),
        fileName: 'themes/concrete/main',
        globalName: 'ConcreteThemeMain',
    },
    {
        entry: path.resolve(__dirname, 'assets/themes/atomik/js/main.js'),
        fileName: 'themes/atomik/main',
        globalName: 'AtomikThemeMain',
    },
]

const cssTargets = [
    {
        entry: path.resolve(__dirname, 'vite/entries/installer-css.js'),
        cssFileName: 'css/installer',
        stubFileName: 'css/installer-stub',
        globalName: 'InstallerCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/cms-app-css.js'),
        cssFileName: 'css/cms/app',
        stubFileName: 'css/cms-app-stub',
        globalName: 'CmsAppCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/cms-page-css.js'),
        cssFileName: 'css/cms/page',
        stubFileName: 'css/cms-page-stub',
        globalName: 'CmsPageCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/concrete-theme-css.js'),
        cssFileName: 'themes/concrete/main',
        stubFileName: 'themes/concrete-main-stub',
        globalName: 'ConcreteThemeCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/atomik-default-css.js'),
        cssFileName: 'themes/atomik/css/skins/default',
        stubFileName: 'themes/atomik/atomik-default-stub',
        globalName: 'AtomikDefaultCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/atomik-rustic-elegance-css.js'),
        cssFileName: 'themes/atomik/css/skins/rustic-elegance',
        stubFileName: 'themes/atomik/atomik-rustic-elegance-stub',
        globalName: 'AtomikRusticEleganceCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/atomik-coastal-breeze-css.js'),
        cssFileName: 'themes/atomik/css/skins/coastal-breeze',
        stubFileName: 'themes/atomik/atomik-coastal-breeze-stub',
        globalName: 'AtomikCoastalBreezeCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/atomik-golden-meadow-css.js'),
        cssFileName: 'themes/atomik/css/skins/golden-meadow',
        stubFileName: 'themes/atomik/atomik-golden-meadow-stub',
        globalName: 'AtomikGoldenMeadowCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/atomik-misty-sage-css.js'),
        cssFileName: 'themes/atomik/css/skins/misty-sage',
        stubFileName: 'themes/atomik/atomik-misty-sage-stub',
        globalName: 'AtomikMistySageCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/atomik-amber-twilight-css.js'),
        cssFileName: 'themes/atomik/css/skins/amber-twilight',
        stubFileName: 'themes/atomik/atomik-amber-twilight-stub',
        globalName: 'AtomikAmberTwilightCssBuild',
    },
    {
        entry: path.resolve(__dirname, 'vite/entries/atomik-midnight-velvet-css.js'),
        cssFileName: 'themes/atomik/css/skins/midnight-velvet',
        stubFileName: 'themes/atomik/atomik-midnight-velvet-stub',
        globalName: 'AtomikMidnightVelvetCssBuild',
    },
]

for (const target of javascriptTargets) {
    await build(createConfig({
        entry: target.entry,
        globalName: target.globalName,
        fileName: target.fileName,
        outDir: concreteRoot,
    }))
}

for (const target of cssTargets) {
    await build(createConfig({
        entry: target.entry,
        globalName: target.globalName,
        fileName: target.stubFileName,
        cssFileName: target.cssFileName,
        outDir: viteTempRoot,
    }))
    copyBuiltCssArtifact(target.cssFileName)
    removeGeneratedScript(viteTempRoot, target.stubFileName)
}

copyVendorAssets()
removeLegacyExtensionlessOutputs()
removeLegacyConcreteViteDirectory()
removeViteTempDirectory()

function createConfig({entry, globalName, fileName, cssFileName = undefined, outDir}) {
    const jsOutputFile = `${fileName}.js`
    const cssOutputFile = cssFileName ? `${cssFileName}.css` : undefined

    return {
        configFile: false,
        root: __dirname,
        publicDir: false,
        mode,
        define: {
            'process.env.NODE_ENV': JSON.stringify(production ? 'production' : 'development'),
            'process.env.LANG': JSON.stringify(process.env.LANG ?? ''),
        },
        plugins: [
            vue({
                customElement: /\.ce\.vue$/,
            }),
        ],
        resolve: {
            preserveSymlinks: true,
            alias: {
                '@concretecms/backendui': backenduiRoot,
                '@concretecms/bedrock': bedrockRoot,
                vue: vueShimPath,
            },
        },
        build: {
            outDir,
            emptyOutDir: false,
            sourcemap: !production,
            minify: production ? 'esbuild' : false,
            watch: watch ? {} : null,
            reportCompressedSize: false,
            cssCodeSplit: false,
            lib: {
                entry,
                formats: ['iife'],
                name: globalName,
                fileName: () => jsOutputFile,
                cssFileName: cssOutputFile,
            },
            rollupOptions: {
                output: {
                    inlineDynamicImports: true,
                },
            },
        },
        css: {
            preprocessorOptions: {
                scss: createScssOptions(),
            },
        },
    }
}

function removeGeneratedScript(rootDir, fileName) {
    const scriptPath = path.join(rootDir, `${fileName}.js`)
    const sourceMapPath = `${scriptPath}.map`
    const legacyScriptPath = path.join(rootDir, fileName)
    const legacySourceMapPath = `${legacyScriptPath}.map`

    if (fs.existsSync(scriptPath)) {
        fs.unlinkSync(scriptPath)
    }
    if (fs.existsSync(sourceMapPath)) {
        fs.unlinkSync(sourceMapPath)
    }
    if (fs.existsSync(legacyScriptPath)) {
        fs.unlinkSync(legacyScriptPath)
    }
    if (fs.existsSync(legacySourceMapPath)) {
        fs.unlinkSync(legacySourceMapPath)
    }
}

function copyBuiltCssArtifact(cssFileName) {
    const relativeCssPath = `${cssFileName}.css`
    const sourceCssPath = resolveBuiltCssArtifact(relativeCssPath)
    const destinationCssPath = path.join(concreteRoot, relativeCssPath)
    copyFile(sourceCssPath, destinationCssPath)

    const sourceMapPath = `${sourceCssPath}.map`
    const destinationMapPath = `${destinationCssPath}.map`
    if (fs.existsSync(sourceMapPath)) {
        copyFile(sourceMapPath, destinationMapPath)
    }
}

function resolveBuiltCssArtifact(expectedRelativePath) {
    const expectedPath = path.join(viteTempRoot, expectedRelativePath)
    if (fs.existsSync(expectedPath)) {
        return expectedPath
    }

    const fallbackPath = path.join(viteTempRoot, 'style.css')
    if (fs.existsSync(fallbackPath)) {
        return fallbackPath
    }

    const candidates = findFilesByExtension(viteTempRoot, '.css')
    if (candidates.length === 1) {
        return candidates[0]
    }

    throw new Error(`Unable to find built CSS artifact for "${expectedRelativePath}" in ${viteTempRoot}`)
}

function removeLegacyExtensionlessOutputs() {
    const legacyFiles = [
        'js/cms',
        'js/cms.map',
        'js/installer',
        'js/installer.map',
        'themes/concrete/main',
        'themes/concrete/main.map',
        'themes/atomik/main',
        'themes/atomik/main.map',
    ]

    for (const relativePath of legacyFiles) {
        const absolutePath = path.join(concreteRoot, relativePath)
        if (fs.existsSync(absolutePath)) {
            fs.unlinkSync(absolutePath)
        }
    }
}

function removeLegacyConcreteViteDirectory() {
    const legacyDirectory = path.join(concreteRoot, '__vite')
    if (fs.existsSync(legacyDirectory)) {
        fs.rmSync(legacyDirectory, {recursive: true, force: true})
    }
}

function removeViteTempDirectory() {
    if (fs.existsSync(viteTempRoot)) {
        fs.rmSync(viteTempRoot, {recursive: true, force: true})
    }
}

function copyVendorAssets() {
    const vueFile = production ? 'vue.global.prod.js' : 'vue.global.js'

    copyFile(
        path.resolve(__dirname, `node_modules/vue/dist/${vueFile}`),
        path.resolve(concreteRoot, 'js/vue.js')
    )
    copyDirectory(
        path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-free/webfonts'),
        path.resolve(concreteRoot, 'css/webfonts')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-free/css/all.css'),
        path.resolve(concreteRoot, 'css/fontawesome/all.css')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'),
        path.resolve(concreteRoot, 'js/bootstrap.js')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map'),
        path.resolve(concreteRoot, 'js/bootstrap.bundle.min.js.map')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/moment/min/moment.min.js'),
        path.resolve(concreteRoot, 'js/moment.js')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/moment/min/moment.min.js.map'),
        path.resolve(concreteRoot, 'js/moment.min.js.map')
    )

    copyDirectory(
        path.resolve(__dirname, 'node_modules/ckeditor4/adapters'),
        path.resolve(concreteRoot, 'js/ckeditor/adapters')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/ckeditor4/ckeditor.js'),
        path.resolve(concreteRoot, 'js/ckeditor/ckeditor.js')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/ckeditor4/config.js'),
        path.resolve(concreteRoot, 'js/ckeditor/config.js')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/ckeditor4/contents.css'),
        path.resolve(concreteRoot, 'js/ckeditor/contents.css')
    )
    copyDirectory(
        path.resolve(__dirname, 'node_modules/ckeditor4/lang'),
        path.resolve(concreteRoot, 'js/ckeditor/lang')
    )
    copyDirectory(
        path.resolve(__dirname, 'node_modules/ckeditor4/plugins'),
        path.resolve(concreteRoot, 'js/ckeditor/plugins')
    )
    copyDirectory(
        path.resolve(__dirname, 'node_modules/ckeditor4/skins'),
        path.resolve(concreteRoot, 'js/ckeditor/skins')
    )
    copyFile(
        path.resolve(__dirname, 'node_modules/ckeditor4/styles.js'),
        path.resolve(concreteRoot, 'js/ckeditor/styles.js')
    )
    copyDirectory(
        path.resolve(__dirname, 'node_modules/ckeditor4/vendor'),
        path.resolve(concreteRoot, 'js/ckeditor/vendor')
    )
}

function copyFile(source, destination) {
    fs.mkdirSync(path.dirname(destination), {recursive: true})
    fs.copyFileSync(source, destination)
}

function copyDirectory(source, destination) {
    fs.mkdirSync(path.dirname(destination), {recursive: true})
    fs.cpSync(source, destination, {recursive: true})
}

function findFilesByExtension(root, extension) {
    if (!fs.existsSync(root)) {
        return []
    }

    const matches = []
    for (const dirent of fs.readdirSync(root, {withFileTypes: true})) {
        const absolutePath = path.join(root, dirent.name)
        if (dirent.isDirectory()) {
            matches.push(...findFilesByExtension(absolutePath, extension))
            continue
        }

        if (dirent.isFile() && absolutePath.endsWith(extension)) {
            matches.push(absolutePath)
        }
    }

    return matches
}

function getArgValue(flag) {
    const index = process.argv.indexOf(flag)
    if (index === -1 || index === process.argv.length - 1) {
        return null
    }

    return process.argv[index + 1]
}
