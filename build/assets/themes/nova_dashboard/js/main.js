import { createApp } from 'vue'

import { createConcretePinia } from '../../../cms/js/stores/pinia'
import Header from './components/Header.vue'

const mountPoints = [
    {
        selector: '[data-nova-dashboard-header]',
        component: Header,
        getProps: (element) => ({
            helpUrl: element.dataset.helpUrl ?? '',
            logoSrc: element.dataset.logoSrc ?? '',
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
