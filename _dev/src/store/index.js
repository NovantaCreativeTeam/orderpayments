import {createStore} from 'vuex'
import orderPaymentApi from "../services/orderPayment.api";
import i18nApi from "../services/i18n.api";
import EventBus from "../utils/event-bus";
import orderInvoiceApi from "../services/orderInvoice.api";

const state = {
    invoices: [],
    payments: [],
    summary: [],
    translations: []
}

const getters = {
    invoices(state) {
        return state.invoices
    },
    payments(state) {
        return state.payments
    },
    summary(state) {
        return state.summary
    }
}
const mutations = {
    setTranslations(state, translations) {
        state.translations = translations
    },
    setPayments(state, payments) {
        state.payments = payments
    },
    setSummary(state, summary) {
        state.summary = summary
    },
    setInvoices(state, invoices) {
        state.invoices = invoices
    }
}
const actions = {
    initialize({state, commit, dispatch}) {
        Promise.all([
            dispatch('loadPayments'),
            dispatch('loadInvoices'),
            dispatch('loadTranslations'),
        ]).then(() => {
            EventBus.emit('order-payments-initialized')
        })
    },
    loadInvoices({state, commit}) {
        return orderInvoiceApi.getAll(id_order).then(response => {
            commit('setInvoices', response)
        })
    },
    loadPayments({state, commit}) {
        return orderPaymentApi.getAll(id_order).then(response => {
            commit('setPayments', response.payments)
            commit('setSummary', {
                currencyIsoCode: response.currencyIsoCode,
                currencySymbol: response.currencySymbol,
                remaining: response.remaining,
                totalOrder: response.totalOrder,
                totalPaid: response.totalPaid,
            })
        })
    },
    loadTranslations({state, commit}) {
        return i18nApi.getTranslations().then(response => {

            let translations = [];
            response.data.data.forEach((t) => {
                translations[t.translation_id] = t.name;
            });

            commit('setTranslations', translations)
        })
    }
}

export default createStore({
    state,
    getters,
    mutations,
    actions
})