<template>
  <Form v-slot="{ errors, handleSubmit }">

    <PsAlert v-show="Object.keys(errors).length > 0" type="danger" class="mb-3">
      <p>{{ trans('correct_and_try_again') }}</p>
      <ul>
        <li v-for="(error, field) in errors">
          <strong>{{field}}</strong>: {{error}}
        </li>
      </ul>
    </PsAlert>
    <slot>
      <form @submit.prevent="handleSubmit(onSubmit)">
      <PSInput
          label="Amount"
          type="number"
          prepend="€"
          v-model="formData.amount"
          validationRules="required"
      />

      <PSInput
          label="Payment Method"
          v-model="formData.paymentMethod"
          validationRules="required"
      />

      <PSDatepicker
          label="Date"
          v-model="formData.date"
          id="payment_date"
          validationRules="required"
      />

      <PSInput
          label="Transaction ID"
          v-model="formData.transactionId"
      />

      <PSSelect
          label="Invoice"
          v-model="formData.invoiceId"
          id="payment_invoice"
          :items="[]"
      />

      <PSFile label="Document" id="payment_document" @change="handleFileUpload"/>

      <div class="actions mt-3">
        <PsSpinner v-if="isLoading"/>
        <div class="ml-auto">
          <PsButton primary type="submit" :disabled="isLoading">{{ trans('save') }}</PsButton>
        </div>
      </div>
    </form>
    </slot>
  </Form>
</template>

<script>
import PSInput from '../widgets/form/ps-input.vue';
import PSDatepicker from '../widgets/form/ps-datepicker.vue';
import PSSelect from '../widgets/form/ps-select.vue';
import PSFile from '../widgets/form/ps-file.vue';
import {Form} from 'vee-validate';
import {defineRule, configure} from 'vee-validate';
import {required, min_value} from '@vee-validate/rules';
import {localize} from '@vee-validate/i18n';

defineRule('required', required);
defineRule('min_value', min_value);

configure({
  generateMessage: localize('it', {
    messages: {
      required: 'This field is required',
      min_value: 'The value must be at least {min}',
    },
  }),
});

import moment from 'moment';
import orderPaymentApi from "../services/orderPayment.api";
import EventBus from "../utils/event-bus";
import PsButton from "../widgets/ps-button.vue";
import PsAlert from "../widgets/ps-alert.vue";
import PsSpinner from "../widgets/ps-spinner.vue";

export default {
  name: 'OrderPaymentForm',
  components: {
    PsSpinner,
    PsAlert,
    PsButton,
    PSInput,
    PSDatepicker,
    PSSelect,
    PSFile,
    Form
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
    }
  },
  data() {
    return {
      formData: {...this.payment},
      isLoading: false,
    };
  },
  watch: {
    payment: {
      handler(newVal) {
        this.formData = {...newVal};
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
    onCancel() {
      this.$emit('cancel')
    },
    onSubmit() {
      this.$emit('submit')
      this.isLoading = true;
      let promise = Promise.resolve();
      if (this.formData.id) {
        promise = orderPaymentApi.update(id_order, this.formData.id, this.formData)
      } else {
        promise = orderPaymentApi.create(id_order, this.formData)
      }

      promise.then(response => {
        this.$emit('saved', {
          id: this.formData.id || null,
          formData: response.data
        });

        EventBus.emit('payment-saved')

        $.growl({message: this.trans('payment_saved')})
      }).catch((error) => {
        this.$emit('error', error);
        $.growl.error({message: error.response.data ? error.response.data.message : this.trans('error')})
      }).finally(() => {
        this.isLoading = false
      })

      // if (this.formData.document) {
      //   data.append('document', this.formData.document);
      // }

    },
  }
};
</script>
