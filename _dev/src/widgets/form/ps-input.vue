<template>
  <ValidationProvider :rules="validationRules" :vid="id" :name="name ?? label" v-slot="{ errors, passed, failed }">
    <div class="form-group"
         :class="{'row': horizontal, 'has-success': passed && validationRules != null, 'has-danger': failed}">
      <label v-if="label" class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{ label }}</label>
      <div :class="{ 'col-sm-9': horizontal}">
        <div :class="{'input-group': append || prepend }">
          <div class="input-group-prepend" v-if="append">
            <span class="input-group-text">{{ append }}</span>
          </div>
          <input :type="type" class="form-control" :disabled="disabled" :value="value" :id="id" @input="onInput"
                 @change="onChange"
                 :class="{
                        'is-valid': passed && validationRules != null,
                        'is-invalid': failed,
                    }"/>
          <div class="input-group-prepend" v-if="prepend">
            <span class="input-group-text">{{ prepend }}</span>
          </div>
        </div>
        <small class="form-text" v-if="helper">{{ helper }}</small>
        <div class="invalid-feedback" v-show="errors.length && failed">
          <span v-for="error in errors" :key="error">{{ error }}</span>
        </div>
      </div>
    </div>
  </ValidationProvider>
</template>

<script>
import {ValidationProvider} from 'vee-validate';

export default {
  model: {
    prop: 'value',
    event: 'input'
  },
  props: {
    id: String,
    name: String,
    value: String | Number,
    type: {
      type: String,
      default: 'text'
    },
    disabled: {
      type: Boolean,
      default: false
    },
    validationRules: {
      type: String | Object,
      required: false
    },
    label: {
      type: String,
      required: false
    },
    helper: String,
    horizontal: {
      type: Boolean,
      default: false
    },
    append: {
      type: String,
      required: false
    },
    prepend: {
      type: String,
      required: false
    }
  },
  methods: {
    onInput(event) {
      this.$emit('input', event.currentTarget.value)
    },
    onChange(event) {
      this.$emit('change', event.currentTarget.value)
    }
  },
  components: {
    ValidationProvider
  }
};

// Reference
// https://www.digitalocean.com/community/tutorials/how-to-add-v-model-support-to-custom-vue-js-components
</script>

<style>
</style>

