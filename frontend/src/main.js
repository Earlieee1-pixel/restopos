import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'

// Sugdi ang Vue app
const app = createApp(App)

// I-attach ang Pinia (state management)
app.use(createPinia())

// I-attach ang router
app.use(router)

app.mount('#app')
