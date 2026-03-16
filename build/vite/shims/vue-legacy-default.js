// We intentionally use the compiler-included bundler build here instead of
// vue.runtime.esm-bundler.js because some legacy Concrete CMS dialogs still
// rely on runtime template compilation after fetching server-rendered HTML.
export * from '../../node_modules/vue/dist/vue.esm-bundler.js'
import * as VueNamespace from '../../node_modules/vue/dist/vue.esm-bundler.js'

export default VueNamespace
