
import { createApp } from 'vue'
import ConcreteInstaller from './components/Installer'
import { createPinia } from "@concretecms/backendui";

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp()
    const pinia = createPinia();
    app.use(pinia);
    app.component('concrete-installer', ConcreteInstaller)
    app.mount('#ccm-page-install')
})
