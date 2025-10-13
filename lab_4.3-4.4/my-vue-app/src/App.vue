<template>
  <div>
    <header class="nav">
      <strong>ИС «Магазин электроники»</strong>
      <div>
        <router-link to="/products">Товары</router-link>
        <router-link to="/clients">Клиенты</router-link>
        <router-link to="/orders">Заказы</router-link>
      </div>
    </header>
    <main class="container">
      <router-view />
    </main>
  </div>
</template>
