<template>
  <va-card>
    <div class="toolbar">
      <h2>Заказы</h2>
      <va-button color="primary" @click="openModal()">Добавить</va-button>
    </div>

    <table class="table">
      <thead><tr><th>ID</th><th>Клиент</th><th>Дата</th><th>Сумма</th><th>Товары</th><th>Действия</th></tr></thead>
      <tbody>
        <tr v-for="o in store.orders" :key="o.id">
          <td>{{o.id}}</td>
          <td>{{store.clientNameById(o.clientId)}}</td>
          <td>{{o.date}}</td>
          <td><strong>{{ money(store.orderTotal(o)) }}</strong></td>
          <td>
            <ul style="margin:0 0 0 18px">
              <li v-for="it in o.items" :key="it.productId">{{ store.productNameById(it.productId) }} — {{ it.qty }} × {{ money(it.price) }}</li>
            </ul>
          </td>
          <td>
            <va-button size="small" @click="openModal(o)">Изменить</va-button>
            <va-button size="small" color="danger" @click="store.deleteOrder(o.id)">Удалить</va-button>
          </td>
        </tr>
      </tbody>
    </table>
  </va-card>

  <va-modal v-model="show" :title="form.id ? 'Изменить заказ' : 'Добавить заказ'" hide-default-actions size="large">
    <div style="display:flex;flex-direction:column;gap:12px">
      <va-select :options="clientOptions" v-model="form.clientId" label="Клиент" placeholder="Выберите клиента" />
      <va-input v-model="form.date" type="date" label="Дата" />

      <va-divider>Товары</va-divider>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
        <va-select :options="productOptions" v-model="draft.productId" label="Товар" style="min-width:260px" />
        <va-input v-model.number="draft.qty" type="number" min="1" step="1" label="Кол-во" style="width:120px" />
        <va-button @click="addItem" :disabled="!draft.productId || draft.qty<1">Добавить позицию</va-button>
      </div>

      <table class="table">
        <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Сумма</th><th>—</th></tr></thead>
        <tbody>
          <tr v-for="(it, idx) in form.items" :key="it.productId">
            <td>{{ store.productNameById(it.productId) }}</td>
            <td>{{ money(it.price) }}</td>
            <td><va-input v-model.number="it.qty" type="number" min="1" step="1" /></td>
            <td>{{ money(it.qty * it.price) }}</td>
            <td><va-button color="danger" size="small" @click="form.items.splice(idx,1)">Удалить</va-button></td>
          </tr>
          <tr v-if="form.items.length===0"><td colspan="5" class="text-muted">Добавьте хотя бы один товар</td></tr>
        </tbody>
      </table>

      <div style="text-align:right;font-weight:600">Итого: {{ money(totalDraft) }}</div>
    </div>

    <div class="modal-footer-fixed">
      <va-button preset="secondary" @click="show=false">Отмена</va-button>
      <va-button color="primary" :disabled="!form.clientId || !form.date || form.items.length===0" @click="save">Сохранить</va-button>
    </div>
  </va-modal>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useShopStore } from '../store/shop'
const store = useShopStore()

const show = ref(false)
const form = reactive({ id:null, clientId:null, date:'', items: [] })
const draft = reactive({ productId:null, qty:1 })

const clientOptions = computed(() => store.clients.map(c => ({ text:c.fullName, value:c.id })))
const productOptions = computed(() => store.products.map(p => ({ text:p.name, value:p.id })))

const totalDraft = computed(() => form.items.reduce((s,it)=>s+it.qty*it.price,0))
const money = (n) => new Intl.NumberFormat('ru-RU',{style:'currency',currency:'RUB',maximumFractionDigits:0}).format(n||0)

function openModal(o){
  if(o){ Object.assign(form, { id:o.id, clientId:o.clientId, date:o.date, items: o.items.map(it=>({ ...it })) }) }
  else { Object.assign(form, { id:null, clientId: store.clients[0]?.id || null, date: new Date().toISOString().slice(0,10), items: [] }) }
  Object.assign(draft, { productId:null, qty:1 })
  show.value = true
}
function addItem(){
  const p = store.productById(draft.productId)
  if(!p) return
  const ex = form.items.find(it => it.productId === p.id)
  if(ex) ex.qty += Number(draft.qty)||1
  else form.items.push({ productId: p.id, qty: Number(draft.qty)||1, price: p.price })
  Object.assign(draft, { productId:null, qty:1 })
}
function save(){
  store.upsertOrder({ ...form, items: form.items })
  show.value = false
}
</script>
