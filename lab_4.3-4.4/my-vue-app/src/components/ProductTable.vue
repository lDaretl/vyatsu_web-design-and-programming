<template>
  <va-card>
    <div class="toolbar">
      <h2>Товары</h2>
      <va-button color="primary" @click="openModal()">Добавить</va-button>
    </div>

    <table class="table">
      <thead><tr><th>ID</th><th>Категория</th><th>Название</th><th>Цена</th><th>Остаток</th><th>Действия</th></tr></thead>
      <tbody>
        <tr v-for="p in store.products" :key="p.id">
          <td>{{p.id}}</td><td>{{p.category}}</td><td>{{p.name}}</td><td>{{ money(p.price) }}</td><td>{{p.stock}}</td>
          <td>
            <va-button size="small" @click="openModal(p)">Изменить</va-button>
            <va-button size="small" color="danger" @click="store.deleteProduct(p.id)">Удалить</va-button>
          </td>
        </tr>
      </tbody>
    </table>
  </va-card>

  <va-modal v-model="show" :title="form.id ? 'Изменить товар' : 'Добавить товар'" hide-default-actions>
    <div style="display:flex;flex-direction:column;gap:10px">
      <va-input v-model="form.category" label="Категория"/>
      <va-input v-model="form.name" label="Название"/>
      <va-input v-model.number="form.price" label="Цена" type="number" min="0"/>
      <va-input v-model.number="form.stock" label="Остаток" type="number" min="0"/>
    </div>
    <div class="modal-footer-fixed">
      <va-button preset="secondary" @click="show=false">Отмена</va-button>
      <va-button color="primary" @click="save">Сохранить</va-button>
    </div>
  </va-modal>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useShopStore } from '../store/shop'
const store = useShopStore()
const show = ref(false)
const form = reactive({id:null,category:'',name:'',price:0,stock:0})
const money = (n) => new Intl.NumberFormat('ru-RU',{style:'currency',currency:'RUB',maximumFractionDigits:0}).format(n||0)
function openModal(p){Object.assign(form,p||{id:null,category:'',name:'',price:0,stock:0});show.value=true}
function save(){store.upsertProduct({...form});show.value=false}
</script>
