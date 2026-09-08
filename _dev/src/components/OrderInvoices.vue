<template>
  <div id="order-invoice-container" class="container-fluid">
    <PSSpinner v-if="isLoading"/>
    <template v-else>
      <PSTable>
        <thead>
        <tr>
          <th>{{ trans('invoice_date') }}</th>
          <th>{{ trans('invoice_number') }}</th>
          <th>{{ trans('payment_method') }}</th>
          <th>{{ trans('payment_term') }}</th>
          <th>{{ trans('invoice_total') }}</th>
          <th>{{ trans('total_paid') }}</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="invoice in invoices" :key="invoice.id">
          <td>{{ $filters.formatDate(invoice.dateAdd) }}</td>
          <td>{{ invoice.number }}</td>
          <td>{{ invoice.paymentMethod ? trans(invoice.paymentMethod) : '---' }}</td>
          <td>{{ trans(invoice.paymentTerm) }} <span v-if="invoice.paymentTerm !== 'total'">{{ invoice.amount || '---'}}</span><span v-if="invoice.paymentTerm !== 'total' && invoice.amount > 0">{{ invoice.amountType === 'percentage' ? '%' : '€'}}</span></td>
          <td>{{ $filters.formatCurrency(invoice.totalToPay) }}</td>
          <td><span class="badge" :class="{ 'badge-danger': invoice.totalPaidTaxIncluded === 0, 'badge-success': invoice.totalPaidTaxIncluded === invoice.totalToPay, 'badge-warning': invoice.totalPaidTaxIncluded > 0 && invoice.totalPaidTaxIncluded !== invoice.totalToPay }">{{ $filters.formatCurrency(invoice.totalPaidTaxIncluded) }}</span></td>
          <td>
            <a class="btn btn-link p-1 dropdown-toggle dropdown-toggle-dots dropdown-toggle-split no-rotate"
               data-toggle="dropdown"></a>
            <div class="ps-dropdown-menu dropdown-menu">
              <a class="dropdown-item" @click="openEditModal(invoice)"><i class="material-icons">edit</i>
                {{ trans('edit') }}</a>
              <a class="dropdown-item" @click="$refs.deleteModal.showModal(); orderInvoiceToDelete = invoice"><i
                  class="material-icons">delete</i> {{ trans('delete') }}</a>
            </div>
            <a class="btn btn-link p-1" v-if="invoice.orderDocumentId" @click="downloadInvoice(invoice.id, invoice.orderDocumentId)"><i class="material-icons">download</i> {{ trans('download') }}</a>
          </td>
        </tr>
        <tr v-if="invoices.length === 0">
          <td colspan="7" class="text-center">{{ trans('no_invoices_found') }}</td>
        </tr>
        </tbody>
      </PSTable>
      <div class="actions">
        <PSSpinner v-show="isSubmitting"/>
        <PSButton class="add-invoice" @click.native="openCreateModal" primary>{{ trans('create_invoice') }}</PSButton>
      </div>

    </template>

    <PSModal :translations="modalTranslations" ref="invoiceModal">
      <OrderInvoiceForm
          v-if="orderInvoiceToEdit && Object.keys(orderInvoiceToEdit).length > 0"
          :invoice="orderInvoiceToEdit"
          @saved="onOrderInvoiceSaved"
      />
    </PSModal>

    <PSModal ref="deleteModal" @save="deleteInvoice" @leave="$refs.deleteModal.hideModal(); orderInvoiceToDelete = null"
             :isLoading="isSubmitting"
             :translations="{ modal_title: trans('are_sure'), button_save: trans('delete'), button_leave: trans('cancel')}">
      {{ trans('delete_order_invoice_confirm') }}
    </PSModal>
  </div>

</template>

<script>

import {defineComponent} from "vue";
import PSSpinner from "../widgets/ps-spinner.vue";
import PSTable from "../widgets/ps-table/ps-table.vue";
import {mapGetters} from "vuex";
import EventBus from "../utils/event-bus";
import PSModal from "../widgets/ps-modal.vue";
import moment from "moment";
import PSButton from "../widgets/ps-button.vue";
import api from "../services/orderInvoice.api";
import OrderInvoiceForm from "./OrderInvoiceForm.vue";
import FileDownload from "js-file-download";

export default {
  name: "OrderInvoicesApp",
  components: {OrderInvoiceForm, PSButton, PSModal, PSTable, PSSpinner},
  computed: {
    ...mapGetters(["invoices"]),
  },
  data() {
    return {
      isLoading: true,
      isSubmitting: false,
      orderInvoiceToEdit: null,
      orderInvoiceToDelete: null,
      modalTranslations: {
        modal_title: this.trans('add_new_invoice'),
      }
    }
  },
  mounted() {
    EventBus.on("order-payments-initialized", () => {
      this.isLoading = false;
      $('#order-invoices-counter').text('(' + this.invoices.length + ')');
    });
  },
  methods: {
    openCreateModal() {
      this.orderInvoiceToEdit = {
        id: null,
        paymentMethod: '',
        paymentTerm: '',
        amountType: 'amount',
        amount: 0,
        deliveryDate: '',
        note: '',
      };
      this.modalTranslations.modal_title = this.trans('new_invoice');
      this.$refs.invoiceModal.showModal();
    },
    openEditModal(invoice) {
      this.orderInvoiceToEdit = {
        id: invoice.id,
        paymentMethod: invoice.paymentMethod,
        paymentTerm: invoice.paymentTerm,
        amountType: invoice.amountType || 'amount',
        amount: invoice.amount,
        deliveryDate: invoice.deliveryDate,
        note: invoice.note,
      };
      this.modalTranslations.modal_title = this.trans('edit_invoice');
      this.$refs.invoiceModal.showModal();
    },
    onOrderInvoiceSaved() {
      this.$store.dispatch('loadInvoices').finally(() => {
        this.$refs.invoiceModal.hideModal();
        this.orderInvoiceToEdit = null;
        this.isSubmitting = false;
      });
    },
    deleteInvoice() {
      this.isSubmitting = true;
      const invoiceId = this.orderInvoiceToDelete.id;
      api.delete(id_order, invoiceId).then((response) => {
        this.$store.dispatch('loadInvoices').finally(() => {
          this.isSubmitting = false
          this.$refs.deleteModal.hideModal();
          this.orderInvoiceToDelete = null;
        });
      }).catch((error) => {
        $.growl.error({message: error.response.data ? error.response.data.message : this.trans('error')})
      }).finally(() => {
        this.isSubmitting = false;
      })
    },
    downloadInvoice(orderInvoiceId, documentId) {
      api.download(id_order, orderInvoiceId, documentId)
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
}
</script>

<style scoped lang="scss">

</style>