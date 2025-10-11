const { createApp } = Vue;

createApp({
    data() {
        return {
            product: 'Socks',
            image: 'https://www.vuemastery.com/images/challenges/vmSocks-green-onWhite.jpg',
            inStock: true,
            onSale: true
        }
    }
}).mount('#app');