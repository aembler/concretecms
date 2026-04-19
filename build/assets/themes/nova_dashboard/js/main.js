import { createApp, defineCustomElement } from 'vue'

import { createConcretePinia } from '../../../cms/js/src/Store/pinia'
import ConcreteBackendForm from './components/Backend/ConcreteBackendForm.ce.vue'
import ConcreteBackendFormActions from './components/Backend/ConcreteBackendFormActions.ce.vue'
import Header from './components/Header.vue'
import Navigation from './components/Navigation.vue'

const parseJsonProp = (value, fallback) => {
    if (!value) {
        return fallback
    }

    try {
        return JSON.parse(value)
    } catch (error) {
        console.error('Unable to parse Nova Dashboard JSON prop.', error)
        return fallback
    }
}

const mountPoints = [
    {
        selector: '[data-nova-dashboard-header]',
        component: Header,
        getProps: (element) => ({
            addonsUrl: element.dataset.addonsUrl ?? '',
            extendUrl: element.dataset.extendUrl ?? '',
            extendThemesUrl: element.dataset.extendThemesUrl ?? '',
            extendUpdateUrl: element.dataset.extendUpdateUrl ?? '',
            helpUrl: element.dataset.helpUrl ?? '',
            logoSrc: element.dataset.logoSrc ?? '',
            settingsUrl: element.dataset.settingsUrl ?? '',
        }),
    },
    {
        selector: '[data-nova-dashboard-navigation]',
        component: Navigation,
        getProps: (element) => ({
            items: parseJsonProp(element.dataset.navigation, []),
        }),
    },
]

const pinia = createConcretePinia()

mountPoints.forEach(({ selector, component, getProps = () => ({}) }) => {
    document.querySelectorAll(selector).forEach((element) => {
        createApp(component, getProps(element))
            .use(pinia)
            .mount(element)
    })
})

const customElementsToRegister = [
    ['concrete-backend-form', defineCustomElement(ConcreteBackendForm, { shadowRoot: false })],
    ['concrete-backend-form-actions', defineCustomElement(ConcreteBackendFormActions, { shadowRoot: false })],
]

customElementsToRegister.forEach(([tagName, component]) => {
    if (!customElements.get(tagName)) {
        customElements.define(tagName, component)
    }
})
