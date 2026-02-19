
import { createApp } from 'vue'
import ConcreteInstaller from './components/Installer'

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp()
    app.component('concrete-installer', ConcreteInstaller)
    app.mount('#ccm-page-install')
})
