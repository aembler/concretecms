import { createApp } from 'vue'

import { createConcretePinia } from '../../../cms/js/stores/pinia'
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
