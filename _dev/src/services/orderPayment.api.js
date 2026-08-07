import axios from 'axios';

const api = {
  getAll(orderId) {
    const url = window.prestashop.instance.router.generate('order_payment_get_all', { orderId });
    return axios.get(url).then(response => response.data);
  },

  create(orderId, payment) {
    const url = window.prestashop.instance.router.generate('order_payment_add', { orderId });
    return axios.post(url, payment, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then(response => response.data);
  },

  update(orderId, paymentId, payment) {
    const url = window.prestashop.instance.router.generate('order_payment_edit', { orderId, paymentId });
    return axios.post(url, payment, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then(response => response.data);
  },

  delete(orderId, paymentId) {
    const url = window.prestashop.instance.router.generate('order_payment_delete', { 'orderId': orderId, 'paymentId': paymentId });
    return axios.delete(url).then(response => response.data);
  },

  download(paymentId) {
    return window.prestashop.instance.router.generate('order_payment_download', { paymentId });
  }
};

export default api;
