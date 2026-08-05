import axios from 'axios';

const api = {
  getAll(orderId) {
    const url = window.prestashop.instance.router.generate('order_payment_list', { orderId });
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

  update(paymentId, payment) {
    const url = window.prestashop.instance.router.generate('order_payment_edit', { paymentId });
    return axios.post(url, payment, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then(response => response.data);
  },

  delete(paymentId) {
    const url = window.prestashop.instance.router.generate('order_payment_delete', { paymentId });
    return axios.delete(url).then(response => response.data);
  },

  download(paymentId) {
    return window.prestashop.instance.router.generate('order_payment_download', { paymentId });
  }
};

export default api;
