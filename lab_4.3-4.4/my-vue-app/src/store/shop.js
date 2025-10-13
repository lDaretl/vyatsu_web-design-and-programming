import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useShopStore = defineStore('shop', () => {
  const products = ref(JSON.parse(localStorage.getItem('products')||'null') || [
    { id:1, category:'Смартфоны', name:'iPhone 15', price: 99990, stock: 10 },
    { id:2, category:'Ноутбуки', name:'MacBook Air M2', price: 129990, stock: 5 },
    { id:3, category:'Наушники', name:'AirPods Pro 2', price: 29990, stock: 15 },
  ])
  const clients = ref(JSON.parse(localStorage.getItem('clients')||'null') || [
    { id:1, fullName:'Иванов Иван', phone:'+7 900 111-11-11', email:'ivanov@example.com' },
    { id:2, fullName:'Петров Петр', phone:'+7 900 222-22-22', email:'petrov@example.com' },
  ])
  const orders = ref(JSON.parse(localStorage.getItem('orders')||'null') || [
    { id:1, clientId:1, date:'2025-10-13', items:[{ productId:1, qty:1, price:99990 }] },
  ])

  const nextId = (arr) => arr.length ? Math.max(...arr.map(x=>x.id)) + 1 : 1

  const productById = (id) => products.value.find(p => p.id === id)
  const productNameById = (id) => productById(id)?.name || '—'
  const clientNameById = (id) => clients.value.find(c => c.id === id)?.fullName || '—'
  const orderTotal = (o) => o.items.reduce((s,it)=>s + it.qty * it.price, 0)

  function upsertProduct(p){ if(p.id){ const i=products.value.findIndex(x=>x.id===p.id); if(i>-1) products.value[i] = { ...products.value[i], ...p } } else { products.value.push({ id: nextId(products.value), ...p }) } }
  function deleteProduct(id){ products.value = products.value.filter(p=>p.id!==id); orders.value.forEach(o=>o.items=o.items.filter(it=>it.productId!==id)) }

  function upsertClient(c){ if(c.id){ const i=clients.value.findIndex(x=>x.id===c.id); if(i>-1) clients.value[i] = { ...clients.value[i], ...c } } else { clients.value.push({ id: nextId(clients.value), ...c }) } }
  function deleteClient(id){ clients.value = clients.value.filter(c=>c.id!==id); orders.value = orders.value.filter(o=>o.clientId!==id) }

  function upsertOrder(o){ if(o.id){ const i=orders.value.findIndex(x=>x.id===o.id); if(i>-1) orders.value[i] = { ...o, items: o.items.map(it=>({...it})) } } else { orders.value.push({ id: nextId(orders.value), ...o, items: o.items.map(it=>({...it})) }) } }
  function deleteOrder(id){ orders.value = orders.value.filter(o=>o.id!==id) }

  watch([products, clients, orders], () => {
    localStorage.setItem('products', JSON.stringify(products.value))
    localStorage.setItem('clients', JSON.stringify(clients.value))
    localStorage.setItem('orders', JSON.stringify(orders.value))
  }, { deep:true })

  return { products, clients, orders, productById, productNameById, clientNameById, orderTotal,
    upsertProduct, deleteProduct, upsertClient, deleteClient, upsertOrder, deleteOrder }
})
