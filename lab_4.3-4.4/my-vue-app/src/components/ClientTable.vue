<template>
  <va-card>
    <div class="toolbar">
      <h2>Клиенты</h2>
      <va-button color="primary" @click="openModal()">Добавить</va-button>
    </div>

    <table class="table">
      <thead><tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>E-mail</th><th>Действия</th></tr></thead>
      <tbody>
        <tr v-for="c in store.clients" :key="c.id">
          <td>{{c.id}}</td><td>{{c.fullName}}</td><td>{{c.phone}}</td><td>{{c.email}}</td>
          <td>
            <va-button size="small" @click="openModal(c)">Изменить</va-button>
            <va-button size="small" color="danger" @click="store.deleteClient(c.id)">Удалить</va-button>
          </td>
        </tr>
      </tbody>
    </table>
  </va-card>

  <va-modal v-model="show" :title="form.id ? 'Изменить клиента' : 'Добавить клиента'" hide-default-actions>
    <div style="display:flex;flex-direction:column;gap:10px">
      <va-input v-model="form.fullName" label="ФИО"/>
      <va-input v-model="form.phone" label="Телефон"/>
      <va-input v-model="form.email" label="E-mail"/>
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
const form = reactive({id:null,fullName:'',phone:'',email:''})
function openModal(c){Object.assign(form,c||{id:null,fullName:'',phone:'',email:''});show.value=true}
function save(){store.upsertClient({...form});show.value=false}
</script>
