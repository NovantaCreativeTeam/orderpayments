import {createApp} from 'vue';
import OrderPayments from './components/OrderPayments.vue';
import Translation from './mixins/translate'
import customRoutes from './services/fos_js_module_routes.json'
import moment from "moment";
import OrderInvoices from "./components/OrderInvoices.vue";

const orderPaymentApp = createApp(OrderPayments)
orderPaymentApp.mixin(Translation)
orderPaymentApp.config.globalProperties.$filters = {
  formatDate(value) {
    return moment(String(value)).format('DD/MM/YYYY')
  },
  formatDateTime(value) {
    return moment(String(value)).format('DD/MM/YYYY hh:mm')
  }
}

const orderInvoicesApp = createApp(OrderInvoices)
orderInvoicesApp.mixin(Translation)
orderInvoicesApp.config.globalProperties.$filters = {
  formatDate(value) {
    return moment(String(value)).format('DD/MM/YYYY')
  },
  formatDateTime(value) {
    return moment(String(value)).format('DD/MM/YYYY hh:mm')
  }
}


$(document).ready(function () {
  window.prestashop.customRoutes = customRoutes
  window.prestashop.component.initComponents([
    'Router'
  ]);

  store.dispatch('initialize');

  orderPaymentApp.mount('#order-payments-app')
  orderInvoicesApp.mount('#order-invoices-app')
});