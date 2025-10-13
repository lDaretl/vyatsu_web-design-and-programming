import { createRouter, createWebHistory } from 'vue-router'
import ProductTable from '../components/ProductTable.vue'
import ClientTable from '../components/ClientTable.vue'
import OrdersTable from '../components/OrdersTable.vue'

export default createRouter({
  history: createWebHistory(),
  routes: [
    { path:'/', redirect:'/products' },
    { path:'/products', component: ProductTable },
    { path:'/clients', component: ClientTable },
    { path:'/orders', component: OrdersTable },
  ]
})
