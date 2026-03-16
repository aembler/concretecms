
import { createApp } from 'vue'
import ConcreteInstaller from './components/Installer.vue'
import { createPinia } from "@concretecms/backendui";

document.addEventListener('DOMContentLoaded', () => {
    const host = document.getElementById('ccm-page-install')
    const propsElement = document.getElementById('ccm-page-install-props')
    if (!(host instanceof HTMLElement) || !(propsElement instanceof HTMLScriptElement)) {
        return
    }

    const app = createApp(ConcreteInstaller, getProps(propsElement))
    const pinia = createPinia();
    app.use(pinia);
    app.mount(host)
})

function getProps(element) {
    const contents = element.textContent?.trim()
    if (!contents) {
        return {}
    }

    try {
        return JSON.parse(contents)
    } catch (error) {
        console.error('Unable to parse installer props.', error)
        return {}
    }
}
