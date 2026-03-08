/*
// Include core libraries for panels, etc...
import "@concretecms/bedrock/assets/cms/js/base";

// Make sure things that need vue contexts to fire automatically do so
// Note - we can't just include this in base.js because then when base.js is
// included in dashboard main.js it fires too early. So we have to separate it out.
$(function() {
    $('[data-vue]').concreteVue({'context': 'cms'})
})
 */

// cms.js
import { defineCustomElement, createApp } from 'vue'
import ConcreteApp from './components/App.ce.vue'
import ConcreteArea from './components/Area.ce.vue'
import ConcreteBlock from './components/Block.ce.vue'
import ConcreteContainer from './components/Container.ce.vue'
import ConcreteAreaBlockTarget from './components/AreaBlockTarget.ce.vue'

import rawCss from '!raw-loader!./../../../../concrete/css/cms/app.css'
import postcss from 'postcss'
import { createConcretePinia } from './stores/pinia'
import { ConcreteAssetLoader } from './support/LegacyAssetLoader'

// Extract only @property rules
function extractPropertyRules(css) {
    const root = postcss.parse(css)
    const propertyRules = []

    root.each(node => {
        if (node.type === 'atrule' && node.name === 'property') {
            propertyRules.push(node.toString())
            node.remove() // remove from original css
        }
    })

    return {
        cleanedCss: root.toString(), // rest of css without @property
        propertyCss: propertyRules.join('\n')
    }
}

// Inject styles into <head>
function injectGlobalStyles(css) {
    const tag = document.createElement('style')
    tag.setAttribute('data-global-tw-property', 'true')
    tag.textContent = css
    document.head.appendChild(tag)
}

// Process the raw CSS
const { cleanedCss, propertyCss } = extractPropertyRules(rawCss)
injectGlobalStyles(propertyCss)

const app = createApp();
const pinia = createConcretePinia();
app.use(pinia);

// For custom elements we must merge component-local SFC styles with the shared
// backend UI stylesheet. Passing only `styles: [cleanedCss]` can overwrite the
// CE's compiled local styles, which makes `<style>` blocks inside `.ce.vue`
// components appear to be ignored.
function getMergedElementStyles(component) {
    const localStyles = Array.isArray(component?.styles) ? component.styles : []
    return [...localStyles, cleanedCss]
}

// Master App component. This component hosts all UI components, including block editors. It has full access
// To tailwind classes. It uses shadow DOM so that page theme styles do not affect it, unlike page level components
// like ConcreteArea and ConcreteContainer, which inherit their styles from the theme and from cms/page.css
const ConcreteAppElement = defineCustomElement(ConcreteApp, {
    styles: getMergedElementStyles(ConcreteApp),
    plugins: [pinia]
})
const ConcreteAreaElement = defineCustomElement(ConcreteArea, {
    shadowRoot: false,
    plugins: [pinia]
})
const ConcreteContainerElement = defineCustomElement(ConcreteContainer, {
    shadowRoot: false,
    plugins: [pinia]
})





const ConcreteBlockElement = defineCustomElement(ConcreteBlock, {
    styles: getMergedElementStyles(ConcreteBlock),
    plugins: [pinia]
})
const ConcreteAreaBlockTargetElement = defineCustomElement(ConcreteAreaBlockTarget, {
    styles: getMergedElementStyles(ConcreteAreaBlockTarget),
    plugins: [pinia]
})

customElements.define('concrete-app', ConcreteAppElement)
customElements.define('concrete-area', ConcreteAreaElement)
customElements.define('concrete-container', ConcreteContainerElement)
customElements.define('concrete-block', ConcreteBlockElement)
customElements.define('concrete-area-block-target', ConcreteAreaBlockTargetElement)

// Legacy compatibility global for classic blocks that dynamically load CSS/JS.
window.ConcreteAssetLoader = ConcreteAssetLoader
