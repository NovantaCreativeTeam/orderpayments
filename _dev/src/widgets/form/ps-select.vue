<template>
        <ValidationProvider :rules="validationRules" :vid="id" :name="name ?? label" v-slot="{ errors, passed, failed }">
        <div class="form-group" :class="{'row': horizontal, 'has-success': passed && validationRules != null, 'has-danger': failed}">
            <label class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{label}}</label>
            <div :class="{ 'col-sm-9': horizontal }">
                <select class="form-control custom-select"
                    :class="{
                        'is-valid': passed && validationRules != null,
                        'is-invalid': failed
                    }"
                    :id="id"
                    :name="id"
                    :disabled="disabled"
                    @change="onChange">
                    <option value="">Select a value</option>
                    <option v-for="(item, index) in items" :key="index" :value="item[itemId]" :selected="item[itemId] == model">{{ item[itemName] }}</option>
                </select>
                <small class="form-text" v-if="helper">{{ helper }}</small>
                <div class="invalid-feedback" v-show="errors.length && failed">
                    <span v-for="error in errors" :key="error">{{error}}</span>
                </div>
            </div>
        </div>
        </ValidationProvider>
</template>

<script>
import { ValidationProvider, validate } from 'vee-validate';

export default {
    model: {
      prop: 'model',
      event: 'change',
    },
    props: {
        id: String,
        name: String,
        model: String|Number,
        disabled: {
            type: Boolean,
            default: false
        },
        validationRules: {
            type: String|Object,
            required:false
        },
        label: {
            type: String,
            required: true
        },
        helper: String,
        horizontal: {
            type: Boolean,
            default: false
        },
        items: {
            type: Array,
            required: true,
        },
        itemId: {
            type: String,
            required: false,
            default: "id",
        },
        itemName: {
            type: String,
            required: false,
            default: "name",
        },
    },
    methods: {
        onChange(event) {
            validate(event.target.value)
            this.$emit('change', event.target.value)
        }
    },
    components: {
        ValidationProvider
    }
};
</script>

<style lang="sass" scoped>
</style>
