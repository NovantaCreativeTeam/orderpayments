<template>
  <Field :rules="validationRules" :name="name ?? label" v-model="internalValue" v-slot="{ field, errors, meta }">
        <div class="form-group" :class="{'row': horizontal, 'has-success': meta.valid && validationRules != null, 'has-danger': meta.touched && !meta.valid}">
            <label v-if="label" class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{label}}</label>
            <div :class="{ 'col-sm-9': horizontal }">
                <textarea v-bind="field" class="form-control" :disabled="disabled" :id="id" v-model="internalValue" @change="onChange"
                    :class="{
                        'is-valid': meta.valid && validationRules != null,
                        'is-invalid': meta.touched && !meta.valid,
                    }"/>
                <small class="form-text" v-if="helper">{{ helper }}</small>
                <div class="invalid-feedback" v-show="errors.length && meta.touched && !meta.valid">
                    <span v-for="error in errors" :key="error">{{error}}</span>
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
        modelValue: [String, Number],
        disabled: {
            type: Boolean,
            default: false
        },
        validationRules: {
            type: [String, Object],
            required:false
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
            internalValue: this.modelValue
        }
    },
    watch: {
        modelValue(newVal) {
            this.internalValue = newVal;
        },
        internalValue(newVal) {
            this.$emit('update:modelValue', newVal);
        }
    },
    methods: {
        onChange(event) {
            this.$emit('change', event.currentTarget.value)
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

