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
import ConcreteApp from './cms/Concrete/App.ce.vue'
import ConcreteArea from './cms/Concrete/Area.ce.vue'
import ConcreteBlock from './cms/Concrete/Block.ce.vue'
import ConcreteContainer from './cms/Concrete/Container.ce.vue'

import rawCss from '!raw-loader!./../../concrete/css/backendui.css'
import postcss from 'postcss'

// We have to import our pinia from this otherwise we have weird errors where pinia state isn't shared
// across the Concrete web components and the secondary backendui ibrary
import { createPinia } from "@concretecms/backendui";

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
const pinia = createPinia();
app.use(pinia);

const ConcreteAppElement = defineCustomElement(ConcreteApp, {
    styles: [cleanedCss],
    plugins: [pinia]
})
const ConcreteAreaElement = defineCustomElement(ConcreteArea, {
    styles: [cleanedCss],
    plugins: [pinia]
})
const ConcreteBlockElement = defineCustomElement(ConcreteBlock, {
    styles: [cleanedCss],
    plugins: [pinia]
})
const ConcreteContainerElement = defineCustomElement(ConcreteContainer, {
    styles: [cleanedCss],
    plugins: [pinia]
})

customElements.define('concrete-app', ConcreteAppElement)
customElements.define('concrete-area', ConcreteAreaElement)
customElements.define('concrete-block', ConcreteBlockElement)
customElements.define('concrete-container', ConcreteContainerElement)

import { useBlockEditorRegistry } from '@concretecms/backendui';
import ComposableEditor from './cms/Concrete/Block/Editor/ComposableEditor.vue';

const registry = useBlockEditorRegistry();
registry.registerEditorComponent('ComposableEditor', ComposableEditor);
