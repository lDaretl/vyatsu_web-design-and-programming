const { createApp } = Vue;

createApp({
    data() {
        return {
            product: 'Socks',
            description: 'A pair of warm fuzzy socks'
        };
    },
}).mount("#app");