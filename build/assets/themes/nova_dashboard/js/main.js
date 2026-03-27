import { createApp } from 'vue'

import Search from '../../../cms/js/components/Toolbar/Search/Search.vue'

const mountPoints = [
    {
        selector: '[data-nova-dashboard-search]',
        component: Search,
    },
]

mountPoints.forEach(({ selector, component }) => {
    document.querySelectorAll(selector).forEach((element) => {
        createApp(component).mount(element)
    })
})
