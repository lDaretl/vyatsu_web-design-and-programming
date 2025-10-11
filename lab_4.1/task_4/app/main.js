const { createApp } = Vue;

createApp({
    data() {
        return {
            product: 'Socks',
            image: 'https://www.vuemastery.com/images/challenges/vmSocks-green-onWhite.jpg',
            inStock: true,
            details: ['80% cotton', '20% polyester', 'Gender-neutral'],
            variants: [{
                    variantId: 2234,
                    variantColor: 'green'
                },
                {
                    variantId: 2235,
                    variantColor: 'blue'
                }
            ],
            sizes: ['S', 'M', 'L', 'XL', 'XXL', 'XXXL']
        }
    }
}).mount('#app');