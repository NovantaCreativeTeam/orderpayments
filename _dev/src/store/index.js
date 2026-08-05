import {createStore} from 'vuex'
import orderPaymentApi from "../services/orderPayment.api";
import i18nApi from "../services/i18n.api";

const state = {
    invoices: [],
    payments: [],
    translations: []
}

const getters = {
    invoices(state) {
        return state.invoices
    },
    payments(state) {
        return state.payments
    }
}
const mutations = {
    setTranslations(state, translations) {
        state.translations = translations
    }
}
const actions = {
    initialize({state, commit, dispatch}) {
        state.invoices = []
        state.payments = []
        dispatch('getTranslations')
    },
    getOrderInvoices({state}) {
        return state.invoices = []
    },
    getOrderPayments({state}) {
        return orderPaymentApi.getAll(orderId).then(response => state.payments = response.data)
    },
    getTranslations({state, commit}) {
        return i18nApi.getTranslations().then(response => {
            commit('setTranslations', response.data)
        })
    }
}

export default createStore({
    state,
    getters,
    mutations,
    actions
})