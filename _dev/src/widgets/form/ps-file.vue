<template>
  <Field :rules="validationRules" :name="name ?? label" v-slot="{ errors, meta }">
    {{modelValue}}
    <div class="form-group"
         :class="{'row': horizontal, 'has-success': meta.valid && validationRules != null, 'has-danger': meta.touched && !meta.valid}">
      <label v-if="label" class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{ label }}</label>
      <div :class="{ 'col-sm-9': horizontal }">

        <div class="custom-file">
          <input
            type="file"
            id="file"
            class="custom-file-input"
            :disabled="disabled"
            :id="id"
            @input="onInput(meta.validate, $event)"
            @change="onChange(meta.validate, $event)"
            :class="{'is-valid': meta.valid && validationRules != null,'is-invalid': meta.touched && !meta.valid}"
          />

          <label class="custom-file-label">
            {{ fileName ?? trans('choose_file') }}
          </label>
        </div>

        <small class="form-text" v-if="helper">{{ helper }}</small>
        <div class="invalid-feedback" v-show="errors.length && meta.touched && !meta.valid">
          <span v-for="error in errors" :key="error">{{ error }}</span>
        </div>
      </div>
    </div>
  </Field>
</template>

<script>
import { Field } from 'vee-validate';

export default {
  props: {
    id: String,
    name: String,
    modelValue: [String, Object],
    disabled: {
      type: Boolean,
      default: false
    },
    validationRules: {
      type: [String, Object],
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
  data() {
    return {
      fileName: this.modelValue ? this.modelValue.name : null
    }
  },
  methods: {
    onInput(validate, event) {
      validate()
      this.fileName = event.currentTarget.files[0].name
      this.$emit('update:modelValue', event.currentTarget.value)
    },
    onChange(validate, event) {
      validate()
      this.fileName = event.currentTarget.files[0].name
      this.$emit('change', event.target.files)
    }
  },
  components: {
    Field
  }
};

// Reference
// https://www.digitalocean.com/community/tutorials/how-to-add-v-model-support-to-custom-vue-js-components
</script>

<style>
</style>

