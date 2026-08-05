<template>
  <ValidationObserver v-slot="{ handleSubmit }">
    <form @submit.prevent="handleSubmit(onSubmit)">
      <PSInput
        label="Amount"
        type="number"
        v-model="formData.amount"
        validation-rules="required|min_value:0.01"
      />

      <PSInput
        label="Payment Method"
        v-model="formData.paymentMethod"
        validation-rules="required"
      />

      <PSDatepicker
        label="Date"
        v-model="formData.date"
        id="payment_date"
        validation-rules="required"
      />

      <PSInput
        label="Transaction ID"
        v-model="formData.transactionId"
      />

      <PSSelect
        label="Invoice"
        v-model="formData.invoiceId"
        id="payment_invoice"
        :items="invoiceItems"
      />

      <PSFile label="Document" id="payment_document" @change="handleFileUpload"/>

      <div class="text-right mt-3">
        <button type="submit" class="btn btn-primary" :disabled="loading">{{ trans('Save') }}</button>
      </div>
    </form>
  </ValidationObserver>
</template>

<script>
import PSInput from '../widgets/form/ps-input.vue';
import PSDatepicker from '../widgets/form/ps-datepicker.vue';
import PSSelect from '../widgets/form/ps-select.vue';
import PSFile from '../widgets/form/ps-file.vue';
import { ValidationObserver } from 'vee-validate';
import moment from 'moment';

export default {
  name: 'OrderPaymentForm',
  components: {
    PSInput,
    PSDatepicker,
    PSSelect,
    PSFile,
    ValidationObserver
  },
  props: {
    payment: {
      type: Object,
      required: false,
      default: () => ({
        amount: 0,
        paymentMethod: '',
        date: moment().format('DD/MM/YYYY'),
        transactionId: '',
        invoiceId: 0,
        document: null
      })
    },
    invoices: {
      type: Array,
      required: true
    },
    currencySymbol: {
      type: String,
      default: ''
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      formData: { ...this.payment }
    };
  },
  computed: {
    invoiceItems() {
      const items = [{ id: 0, name: '-- None --' }];
      this.invoices.forEach(invoice => {
        const name = (invoice.note ? invoice.note : 'Invoice #' + invoice.number) + ' (' + this.formatCurrency(invoice.total) + ')';
        items.push({
          id: invoice.id,
          name: name
        });
      });
      return items;
    }
  },
  watch: {
    payment: {
      handler(newVal) {
        this.formData = { ...newVal };
        if (this.formData.date && this.formData.date.includes('T')) {
           this.formData.date = moment(this.formData.date).format('DD/MM/YYYY');
        }
      },
      deep: true
    }
  },
  methods: {
    handleFileUpload(files) {
      if (files && files.length > 0) {
        this.formData.document = files[0];
      }
    },
    onSubmit() {
      const data = new FormData();
      data.append('amount', this.formData.amount);
      data.append('payment_method', this.formData.paymentMethod);
      
      // Convert date back to YYYY-MM-DD for backend
      const formattedDate = moment(this.formData.date, 'DD/MM/YYYY').format('YYYY-MM-DD HH:mm:ss');
      data.append('date', formattedDate);
      
      data.append('transaction_id', this.formData.transactionId || '');
      data.append('id_invoice', this.formData.invoiceId);
      
      if (this.formData.document) {
        data.append('document', this.formData.document);
      }

      this.$emit('save', {
        id: this.formData.id || null,
        formData: data
      });
    },
    formatCurrency(value) {
      return this.currencySymbol + ' ' + parseFloat(value).toFixed(2);
    },
  },
  created() {
     if (this.formData.date && this.formData.date.includes('T')) {
        this.formData.date = moment(this.formData.date).format('DD/MM/YYYY');
     }
  }
};
</script>
