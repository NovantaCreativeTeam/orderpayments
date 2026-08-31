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
        <div class="row">
          <div class="col-md-6">
            <PSSelect
                :label="trans('payment_method')"
                name="payment_method"
                v-model="formData.paymentMethod"
                :items="availablePaymentMethods"
                validationRules="required"
            />
          </div>
          <div class="col-md-6">
            <PSSelect
                :label="trans('payment_term')"
                name="payment_term"
                v-model="formData.paymentTerm"
                :items="availablePaymentTerms"
                validationRules="required"
            />
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <PSSelect
                :label="trans('amount_type')"
                name="amount_type"
                v-model="formData.amountType"
                id="amount_type"
                :items="amountTypeOptions"
                v-if="formData.paymentTerm === 'advance' || formData.paymentTerm === 'down'"
            />
          </div>
          <div class="col-md-6">
            <PSInput
                :label="trans('amount')"
                name="amount"
                type="number"
                v-model="formData.amount"
                v-if="formData.paymentTerm === 'advance' || formData.paymentTerm === 'down'"
            />
          </div>
        </div>

        <PSDatepicker
            :label="trans('delivery_date')"
            name="delivery_date"
            v-model="formData.deliveryDate"
            id="delivery_date"
            validationRules="required"
        />

        <PsTextarea
            :label="trans('note')"
            name="note"
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
        deliveryDate: '',
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