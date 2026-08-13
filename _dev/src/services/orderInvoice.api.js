import axios from 'axios';

const orderInvoiceApi = {
  getAll(orderId) {
    const url = window.prestashop.instance.router.generate('order_invoice_get_all', { orderId });
    return axios.get(url).then(response => response.data);
  },

  create(orderId, invoice) {
    const url = window.prestashop.instance.router.generate('order_invoice_add', { orderId });
    return axios.post(url, invoice, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then(response => response.data);
  },

  update(orderId, orderInvoiceId, invoice) {
    const url = window.prestashop.instance.router.generate('order_invoice_edit', { orderId, orderInvoiceId });
    return axios.post(url, invoice, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then(response => response.data);
  },

  delete(orderId, orderInvoiceId) {
    const url = window.prestashop.instance.router.generate('order_invoice_delete', { 'orderId': orderId, 'orderInvoiceId': orderInvoiceId });
    return axios.delete(url).then(response => response.data);
  },

  download(orderId, orderInvoiceId, documentId) {
    const url = window.prestashop.instance.router.generate('order_invoice_download', { 'orderId': orderId, 'orderInvoiceId': orderInvoiceId, 'documentId': documentId });
    return axios.get(url, {responseType: 'blob'})
  }
};

export default orderInvoiceApi;
