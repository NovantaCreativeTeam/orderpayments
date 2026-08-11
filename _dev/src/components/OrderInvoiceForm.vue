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
        <PSSelect
            label="Payment Method"
            v-model="formData.paymentMethod"
            :items="availablePaymentMethods"
            validationRules="required"
        />

        <PSSelect
            label="Payment Term"
            v-model="formData.paymentTerm"
            :items="availablePaymentTerms"
            validationRules="required"
        />

        <PSSelect
            label="Amount Type"
            v-model="formData.amountType"
            id="amount_type"
            :items="amountTypeOptions"
            v-if="formData.paymentTerm === 'advance' || formData.paymentTerm === 'down'"
        />

        <PSInput
            label="Amount"
            type="number"
            v-model="formData.amount"
            v-if="formData.paymentTerm === 'advance' || formData.paymentTerm === 'down'"
        />

        <PSDatepicker
            label="Shipping Date"
            v-model="formData.shippingDate"
            id="shipping_date"
            validationRules="required"
        />

        <PsTextarea
            label="Note"
            type="textarea"
            v-model="formData.note"
        />

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
import orderInvoiceApi from "../services/orderInvoice.api";
import EventBus from "../utils/event-bus";
import PsButton from "../widgets/ps-button.vue";
import PsAlert from "../widgets/ps-alert.vue";
import PsSpinner from "../widgets/ps-spinner.vue";
import PsTextarea from "../widgets/form/ps-textarea.vue";

export default {
  name: 'OrderInvoiceForm',
  components: {
    PsTextarea,
    PsSpinner,
    PsAlert,
    PsButton,
    PSInput,
    PSDatepicker,
    PSSelect,
    Form
  },
  props: {
    invoice: {
      type: Object,
      required: false,
      default: () => ({
        id: null,
        paymentMethod: '',
        paymentTerm: '',
        amountType: 'amount',
        amount: 0,
        shippingDate: moment().format('DD/MM/YYYY'),
        note: '',
      })
    }
  },
  data() {
    return {
      formData: {...this.invoice},
      isLoading: false,
      amountTypeOptions: [
        {
          id: 'amount',
          name: this.trans('amount')
        },
        {
          id: 'percentage',
          name: this.trans('percentage')
        }
      ],
      availablePaymentMethods: paymentMethods.map(method => ({id: method, name: this.trans(method)})),
      availablePaymentTerms: paymentTerms.map(term => ({id: term, name: this.trans(term)}))
    };
  },
  watch: {
    invoice: {
      handler(newVal) {
        this.formData = {...newVal};
        if (this.formData.shippingDate && this.formData.shippingDate.includes('T')) {
          this.formData.shippingDate = moment(this.formData.shippingDate).format('DD/MM/YYYY');
        }
      },
      deep: true
    }
  },
  methods: {
    onCancel() {
      this.$emit('cancel')
    },
    onSubmit() {
      this.$emit('submit')
      this.isLoading = true;
      let promise = Promise.resolve();
      
      const payload = {
          ...this.formData,
      };

      if (this.formData.id) {
        promise = orderInvoiceApi.update(id_order, this.formData.id, payload)
      } else {
        promise = orderInvoiceApi.create(id_order, payload)
      }

      promise.then(response => {
        this.$emit('saved', {
          id: this.formData.id || (response.data && response.data.id) || null,
          formData: response.data || response
        });

        EventBus.emit('invoice-saved')

        $.growl({message: this.trans('invoice_saved')})
      }).catch((error) => {
        this.$emit('error', error);
        $.growl.error({message: error.response && error.response.data ? error.response.data.message : this.trans('error')})
      }).finally(() => {
        this.isLoading = false
      })
    },
  }
};
</script>
<style>

</style>