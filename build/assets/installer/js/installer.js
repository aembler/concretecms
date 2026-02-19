
import { createApp } from 'vue'
import ConcreteInstaller from './components/Installer'
import '@concretecms/backendui/dist/backendui.css'

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp()
    app.component('concrete-installer', ConcreteInstaller)
    app.mount('#ccm-page-install')
})
