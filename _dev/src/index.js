import {createApp} from 'vue';
import store from './store';
import OrderPayments from './components/OrderPayments.vue';
import Translation from './mixins/translate'
import customRoutes from './services/fos_js_module_routes.json'
import moment from "moment";
import OrderInvoices from "./components/OrderInvoices.vue";

const filters = {
  formatDate(value) {
    return moment(String(value)).format('DD/MM/YYYY')
  },
  formatDateTime(value) {
    return moment(String(value)).format('DD/MM/YYYY hh:mm')
  },
  formatCurrency(value, currency = 'EUR', locale = 'it-IT') {
    if (typeof value !== "number") {
      value = parseFloat(value);
    }
    if (isNaN(value)) {
      return value;
    }
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: currency,
    }).format(value);
  }
}

const orderPaymentApp = createApp(OrderPayments, {
  orderId: id_order
})
orderPaymentApp.use(store)
orderPaymentApp.mixin(Translation)
orderPaymentApp.config.globalProperties.$filters = filters

const orderInvoicesApp = createApp(OrderInvoices, {
  orderId: id_order
})
orderInvoicesApp.use(store)
orderInvoicesApp.mixin(Translation)
orderInvoicesApp.config.globalProperties.$filters = filters


window.prestashop.customRoutes = customRoutes

$(document).ready(function () {
  store.dispatch('initialize');

  orderPaymentApp.mount('#order-payments-app')
  $('#view_order_payments_block').remove()

  orderInvoicesApp.mount('#order-invoices-app')
});