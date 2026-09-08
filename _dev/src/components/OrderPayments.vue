<template>
  <div id="order-payment-container" class="container-fluid">
    <PSSpinner v-if="isLoading"/>
    <template v-else>
      <div class="row mb-3 payment-summary">
        <div class="col-md-4 text-center">
          <strong>{{ trans('total_order') }}</strong><br>
          <span class="badge badge-primary">{{ $filters.formatCurrency(summary.totalOrder, summary.currencyIsoCode) }}</span>
        </div>
        <div class="col-md-4 text-center">
          <strong>{{ trans('total_paid')}}</strong><br>
          <span class="badge" :class="{'badge-success': summary.totalPaid === summary.totalOrder, 'badge-warning': summary.totalPaid < summary.totalOrder && summary.totalPaid > 0, 'badge-danger': summary.totalPaid === 0 }">{{ $filters.formatCurrency(summary.totalPaid, summary.currencyIsoCode) }}</span>
        </div>
        <div class="col-md-4 text-center">
          <strong>{{ trans('remaining') }}</strong><br>
          <span class="badge" :class="{'badge-success': summary.remaining === 0, 'badge-danger': summary.remaining > 0}">
            {{ $filters.formatCurrency(summary.remaining, summary.currencyIsoCode) }}
          </span>
        </div>
      </div>

      <PSTable>
        <thead>
        <tr>
          <th>{{ trans('date') }}</th>
          <th>{{ trans('payment_method') }}</th>
          <th>{{ trans('transaction_id') }}</th>
          <th>{{ trans('invoice') }}</th>
          <th>{{ trans('employee') }}</th>
          <th>{{ trans('amount') }}</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="payment in payments" :key="payment.id">
          <td>{{ $filters.formatDate(payment.date) }}</td>
          <td>{{ trans(payment.method) }}</td>
          <td>{{ payment.transactionId || '---' }}</td>
          <td>{{ payment.orderInvoiceNumber || '---' }}</td>
          <td>{{ payment.employee ? payment.employee.fullName : '---' }}</td>
          <td>{{ $filters.formatCurrency(payment.amount, summary.currencyIsoCode) }}</td>
          <td>
            <a class="btn btn-link p-1 dropdown-toggle dropdown-toggle-dots dropdown-toggle-split no-rotate"
               data-toggle="dropdown"></a>
            <div class="ps-dropdown-menu dropdown-menu">
              <a class="dropdown-item" @click="openEditModal(payment)"><i class="material-icons">edit</i>
                {{ trans('edit') }}</a>
              <a class="dropdown-item" @click="$refs.deleteModal.showModal(); orderPaymentToDelete = payment"><i
                  class="material-icons">delete</i> {{ trans('delete') }}</a>
            </div>
            <a class="btn btn-link p-1" v-if="payment.documentId" @click="downloadDocument(payment.id, payment.documentId)"><i
                class="material-icons">download</i></a>
          </td>
        </tr>
        <tr v-if="payments.length === 0">
          <td colspan="8" class="text-center">{{ trans('no_payments_found') }}</td>
        </tr>
        </tbody>
      </PSTable>

      <div class="actions">
        <PSSpinner v-show="isSubmitting"/>
        <PSButton class="add-payment" @click.native="openCreateModal" primary>{{ trans('create_payment') }}</PSButton>
      </div>
    </template>

    <!-- Add/Edit Payment Modal -->
    <PSModal :translations="modalTranslations" ref="paymentModal" @leave="orderPaymentToEdit = null">
      <OrderPaymentForm
          v-if="orderPaymentToEdit && Object.keys(orderPaymentToEdit).length > 0"
          :payment="orderPaymentToEdit"
          @saved="onOrderPaymentSaved"
      />
    </PSModal>

    <PSModal ref="deleteModal" @save="deletePayment" @leave="$refs.deleteModal.hideModal(); orderPaymentToDelete = null"
             :isLoading="isSubmitting"
             :translations="{ modal_title: trans('are_sure'), button_save: trans('delete'), button_leave: trans('cancel')}">
      {{ trans('delete_order_payment_confirm') }}
    </PSModal>
  </div>
</template>

<script>
import api from '../services/orderPayment.api';
import PSModal from '../widgets/ps-modal.vue';
import OrderPaymentForm from './OrderPaymentForm.vue';
import PSSpinner from "../widgets/ps-spinner.vue";
import PSButton from "../widgets/ps-button.vue";
import PSTable from "../widgets/ps-table/ps-table.vue";
import EventBus from "../utils/event-bus";
import {mapGetters} from "vuex";
import moment from "moment/moment";
import FileDownload from "js-file-download";

export default {
  name: 'OrderPaymentApp',
  components: {
    PSTable,
    PSSpinner, PSButton,
    PSModal,
    OrderPaymentForm
  },
  props: {
    orderId: {type: [Number, String], required: true}
  },
  data() {
    return {
      isLoading: true,
      isSubmitting: false,
      orderPaymentToEdit: null,
      orderPaymentToDelete: null,
      modalTranslations: {
        modal_title: this.trans('add_new_payment'),
      }
    };
  },
  computed: {
    ...mapGetters(["payments", "invoices", "summary"]),
  },
  mounted() {
    EventBus.on("order-payments-initialized", () => {
      this.isLoading = false;
      $('#order-payments-counter').text('(' + this.payments.length + ')');
    });
  },
  methods: {
    // async fetchPayments() {
    //   this.isLoading = true;
    //   try {
    //     const data = await api.getAll(this.orderId);
    //     this.payments = data.payments;
    //     this.summary = {
    //       totalOrder: data.totalOrder,
    //       totalPaid: data.totalPaid,
    //       remaining: data.remaining,
    //       currencySymbol: data.currencySymbol,
    //       currencyIsoCode: data.currencyIsoCode
    //     };
    //     this.invoices = data.invoices;
    //   } catch (e) {
    //     console.error("Error fetching payments", e);
    //   } finally {
    //     this.isLoading = false;
    //   }
    // },
    openCreateModal() {
      this.orderPaymentToEdit = {
        amount: 0,
        paymentMethod: '',
        date: moment().format('DD/MM/YYYY'),
        transactionId: '',
        invoiceId: 0,
        document: null
      };
      this.modalTranslations.modal_title = this.trans('new_payment');
      this.$refs.paymentModal.showModal();
    },
    openEditModal(payment) {
      this.orderPaymentToEdit = {
        id: payment.id,
        amount: payment.amount,
        paymentMethod: payment.method,
        date: payment.date.replace(' ', 'T'),
        transactionId: payment.transactionId,
        invoiceId: payment.orderInvoiceId
      };
      this.modalTranslations.modal_title = this.trans('edit_payment');
      this.$refs.paymentModal.showModal();
    },
    onOrderPaymentSaved() {
      Promise.all([
        this.$store.dispatch('loadInvoices'),
        this.$store.dispatch('loadPayments')
      ]).finally(() => {
        this.$refs.paymentModal.hideModal();
        this.orderPaymentToEdit = null;
        this.isSubmitting = false;
      });
    },
    deletePayment() {
      this.isSubmitting = true;
      const paymentId = this.orderPaymentToDelete.id;
      api.delete(id_order, paymentId).then((response) => {
        this.$store.dispatch('loadPayments').finally(() => {
          this.isSubmitting = false
          this.$refs.deleteModal.hideModal();
          this.orderPaymentToDelete = null;
        });
      }).catch((error) => {
        $.growl.error({message: error.response.data ? error.response.data.message : this.trans('error')})
      }).finally(() => {
        this.isSubmitting = false;
      })
    },
    downloadDocument(paymentId, documentId) {
      api.download(id_order, paymentId, documentId)
          .then((response) => {
            let filename = ''
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/
            const matches = filenameRegex.exec(response.headers["content-disposition"])

            if (matches != null && matches[1]) {
              filename = matches[1].replace(/['"]/g, '')
            }

            FileDownload(response.data, filename)
          })
          .catch((error) => {
            $.growl.error({message: error.response?.data?.message || error.message})
          })
    },
  }
};
</script>
<style lang="sass" scoped>

.payment-summary
  background-color: #efefef
  padding: 1rem
  border: 1px solid

.actions
  display: flex
  align-items: center

  .add-payment
    margin-left: auto

</style>
