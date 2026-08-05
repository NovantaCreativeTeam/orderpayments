<template>
  <ValidationProvider :rules="validationRules" :vid="id" :name="name ?? label" v-slot="{ errors, passed, failed, dirty }">
    <div class="form-group"
         :class="{'row': horizontal, 'has-success': passed && validationRules != null, 'has-danger': failed}">
      <label v-if="label" class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{ label }}</label>
      <div :class="{ 'col-sm-9': horizontal }">

        <div class="custom-file">
          <input
            type="file"
            id="file"
            class="custom-file-input"
            :disabled="disabled"
            :id="id"
            @input="onInput"
            @change="onChange"
            :class="{'is-valid': !errors.length,'is-invalid': errors.length && dirty}"
          />

          <label class="custom-file-label">
            {{ value ? value.name : trans('choose_file') }}
          </label>
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
import {ValidationProvider, validate} from 'vee-validate';

export default {
  props: {
    id: String,
    name: String,
    value: String|File,
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
    }
  },
  methods: {
    onInput(event) {
      validate(event)
      this.$emit('input', event.currentTarget.value)
    },
    onChange(event) {
      validate(event)
      this.$emit('change', event.target.files)
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

