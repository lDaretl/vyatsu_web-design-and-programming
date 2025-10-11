const { createApp } = Vue;

createApp({
    data() {
        return {
            product: 'Socks',
            image: 'https://www.vuemastery.com/images/challenges/vmSocks-green-onWhite.jpg',
            link: 'https://www.amazon.com/s/ref=nb_sb_noss?url=search-alias%3Daps&field-keywords=socks'
        }
    }
}).mount('#app');