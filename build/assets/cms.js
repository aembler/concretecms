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
import ConcreteAreaBlockTarget from './cms/Concrete/AreaBlockTarget.ce.vue'

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

// For custom elements we must merge component-local SFC styles with the shared
// backend UI stylesheet. Passing only `styles: [cleanedCss]` can overwrite the
// CE's compiled local styles, which makes `<style>` blocks inside `.ce.vue`
// components appear to be ignored.
function getMergedElementStyles(component) {
    const localStyles = Array.isArray(component?.styles) ? component.styles : []
    return [...localStyles, cleanedCss]
}

const ConcreteAppElement = defineCustomElement(ConcreteApp, {
    styles: getMergedElementStyles(ConcreteApp),
    plugins: [pinia]
})
const ConcreteAreaElement = defineCustomElement(ConcreteArea, {
    styles: getMergedElementStyles(ConcreteArea),
    plugins: [pinia]
})
const ConcreteBlockElement = defineCustomElement(ConcreteBlock, {
    styles: getMergedElementStyles(ConcreteBlock),
    plugins: [pinia]
})
const ConcreteContainerElement = defineCustomElement(ConcreteContainer, {
    styles: getMergedElementStyles(ConcreteContainer),
    plugins: [pinia]
})
const ConcreteAreaBlockTargetElement = defineCustomElement(ConcreteAreaBlockTarget, {
    styles: getMergedElementStyles(ConcreteAreaBlockTarget),
    plugins: [pinia]
})

customElements.define('concrete-app', ConcreteAppElement)
customElements.define('concrete-area', ConcreteAreaElement)
customElements.define('concrete-block', ConcreteBlockElement)
customElements.define('concrete-container', ConcreteContainerElement)
customElements.define('concrete-area-block-target', ConcreteAreaBlockTargetElement)

import { useBlockEditorRegistry } from '@concretecms/backendui';
import ComposableEditor from './cms/Concrete/Block/Editor/ComposableEditor.vue';
import DialogEditor from './cms/Concrete/Block/Editor/DialogEditor.vue';
import InlineEditor from './cms/Concrete/Block/Editor/InlineEditor.vue';

const registry = useBlockEditorRegistry();
registry.registerEditorComponent('ComposableEditor', ComposableEditor);
registry.registerEditorComponent('DialogEditor', DialogEditor);
registry.registerEditorComponent('InlineEditor', InlineEditor);
