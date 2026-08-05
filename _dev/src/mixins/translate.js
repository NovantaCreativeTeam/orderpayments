export default {
    methods: {
        trans(key) {
            return this.$store.state.translations[key];
        },
    },
};