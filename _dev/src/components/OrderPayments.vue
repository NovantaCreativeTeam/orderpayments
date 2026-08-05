<template>
  <div id="order-payment-container">
    <div v-if="loading" class="text-center mt-3">
      <div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <div v-else class="card mt-2">
      <div class="card-header">
        <h3 class="card-header-title">
          Payments
        </h3>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-4 text-center">
            <strong>Total Order</strong><br>
            {{ formatCurrency(summary.totalOrder) }}
          </div>
          <div class="col-md-4 text-center">
            <strong>Total Paid</strong><br>
            <span class="badge badge-success">{{ formatCurrency(summary.totalPaid) }}</span>
          </div>
          <div class="col-md-4 text-center">
            <strong>Remaining</strong><br>
            <span class="badge badge-danger">{{ formatCurrency(summary.remaining) }}</span>
          </div>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Payment Method</th>
              <th>Transaction ID</th>
              <th>Amount</th>
              <th>Document</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in payments" :key="payment.id">
              <td>{{ payment.date }}</td>
              <td>{{ payment.method }}</td>
              <td>{{ payment.transactionId }}</td>
              <td>{{ formatCurrency(payment.amount) }}</td>
              <td>
                <a v-if="payment.document" :href="getDownloadUrl(payment.document_id)" class="btn btn-sm btn-outline-secondary">
                  <i class="material-icons">cloud_download</i> {{ payment.document }}
                </a>
              </td>
      <td>
                <button class="btn btn-sm btn-outline-primary mr-1" @click="showEditModal(payment)">
                  <i class="material-icons">edit</i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-payment" @click="deletePayment(payment.id)">
                  <i class="material-icons">delete</i>
                </button>
              </td>
            </tr>
            <tr v-if="payments.length === 0">
              <td colspan="6" class="text-center">No payments found</td>
            </tr>
          </tbody>
        </table>

        <div class="text-right mt-3">
          <button class="btn btn-primary" @click="showAddModal">
            New Payment
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Payment Modal -->
    <PSModal :translations="modalTranslations" ref="paymentModal">
      <OrderPaymentForm
        :payment="editingPayment"
        :invoices="invoices"
        :currencySymbol="summary.currencySymbol"
        :loading="saving"
        @save="savePayment"
      />
    </PSModal>
  </div>
</template>

<script>
import api from '../services/orderPayment.api';
import PSModal from '../widgets/ps-modal.vue';
import OrderPaymentForm from './OrderPaymentForm.vue';

export default {
  name: 'OrderPaymentApp',
  components: {
    PSModal,
    OrderPaymentForm
  },
  props: {
    orderId: { type: [Number, String], required: true }
  },
  data() {
    return {
      payments: [],
      summary: {
        totalOrder: 0,
        totalPaid: 0,
        remaining: 0,
        currencySymbol: ''
      },
      invoices: [],
      loading: true,
      saving: false,
      editingPayment: null,
      modalTranslations: {
        modal_title: 'Add New Payment'
      }
    };
  },
  mounted() {
    this.fetchPayments();
  },
  methods: {
    async fetchPayments() {
      this.loading = true;
      try {
        const data = await api.getAll(this.orderId);
        this.payments = data.payments;
        this.summary = {
          totalOrder: data.totalOrder,
          totalPaid: data.totalPaid,
          remaining: data.remaining,
          currencySymbol: data.currencySymbol
        };
        this.invoices = data.invoices;
      } catch (e) {
        console.error("Error fetching payments", e);
      } finally {
        this.loading = false;
      }
    },
    showAddModal() {
      this.editingPayment = {
        amount: 0,
        paymentMethod: '',
        date: new Date().toLocaleString('sv-SE').slice(0, 16).replace(' ', 'T'),
        transactionId: '',
        invoiceId: 0,
        document: null
      };
      this.modalTranslations.modal_title = 'Add New Payment';
      this.$refs.paymentModal.showModal();
    },
    showEditModal(payment) {
      this.editingPayment = {
        id: payment.id,
        amount: payment.amount,
        paymentMethod: payment.method,
        date: payment.date.replace(' ', 'T'),
        transactionId: payment.transactionId,
        invoiceId: payment.invoiceId || 0
      };
      this.modalTranslations.modal_title = 'Edit Payment';
      this.$refs.paymentModal.showModal();
    },
    async savePayment({ id, formData }) {
      this.saving = true;
      try {
        let response;
        if (id) {
          response = await api.update(id, formData);
        } else {
          response = await api.create(this.orderId, formData);
        }

        if (response.success) {
          this.$refs.paymentModal.hideModal();
          await this.fetchPayments();
        } else {
          alert(response.message || "Error saving payment");
        }
      } catch (e) {
        alert("Network error while saving payment");
      } finally {
        this.saving = false;
      }
    },
    async deletePayment(paymentId) {
      if (!confirm("Are you sure you want to delete this payment?")) return;

      try {
        const data = await api.delete(paymentId);
        if (data.success) {
          await this.fetchPayments();
        } else {
          alert(data.message || "Error deleting payment");
        }
      } catch (e) {
        alert("Network error while deleting payment");
      }
    },
    getDownloadUrl(paymentId) {
      return api.download(paymentId);
    },
    formatCurrency(value) {
      return this.summary.currencySymbol + ' ' + parseFloat(value).toFixed(2);
    }
  }
};
</script>
