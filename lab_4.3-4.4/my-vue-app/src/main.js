import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createVuestic } from 'vuestic-ui'
import 'vuestic-ui/css'
import App from './App.vue'
import router from './router'
import './styles.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(createVuestic({
  config: {
    colors: { variables: { primary:'#3b82f6', backgroundPrimary:'#f8fafc', backgroundSecondary:'#ffffff', danger:'#ef4444' } },
  }
}))
app.mount('#app')
