<template>
  <Field :rules="validationRules" :name="name ?? label" v-model="dateValue" v-slot="{ errors, meta }">
        <div class="form-group" :class="{'row': horizontal, 'has-success': meta.valid && validationRules != null, 'has-danger': meta.touched && !meta.valid}">
            <label v-if="label" class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{label}}</label>
            <div :class="{ 'col-sm-9': horizontal }">
                <div class="input-group date">
                    <input type="text" ref="datepicker" class="form-control" :disabled="disabled" :id="id" v-model="dateValue"
                        :class="{
                            'is-valid': meta.valid && validationRules != null,
                            'is-invalid': meta.touched && !meta.valid,
                        }"/>
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="material-icons">event</i>
                        </span>
                    </div>
                </div>

                <small class="form-text" v-if="helper">{{ helper }}</small>
                <div class="invalid-feedback" v-show="errors.length && meta.touched && !meta.valid">
                    <span v-for="error in errors" :key="error">{{error}}</span>
                </div>
            </div>
        </div>
    </Field>
</template>

<script>
import { Field } from "vee-validate";
import moment from 'moment'
import 'moment/min/locales'

export default {
    props: {
        id: String,
        name: String,
        modelValue: [String, Number],
        disabled: {
            type: Boolean,
            default: false,
        },
        validationRules: {
            type: [String, Object],
            required: false,
        },
        label: {
            type: String,
            required: false,
        },
        helper: String,
        horizontal: {
            type: Boolean,
            default: false,
        },
    },
    data() {
      return {
        dateValue: this.modelValue
      }
    },
    mounted() {
        $(this.$refs.datepicker).datetimepicker({
            format: 'DD/MM/YYYY',
            date: this.modelValue,
            locale: window.full_language_code
        }).on('dp.change', (infos) => {
            this.dateValue = infos.date.format('DD/MM/YYYY')
            this.$emit('update:modelValue', infos.date.format())
        });
    },
    components: {
        Field
    }
};
</script>

<style lang="sass" scoped>
@import '~@scss/config/_settings.scss'

.date
    a[data-action='clear']::before
        font-family: 'Material Icons'
        content: "\E14C"
        font-size: 20px
        position: absolute
        bottom: 15px
        left: 50%
        margin-left: -10px
        color: $gray-dark
        cursor: pointer
.bootstrap-datetimepicker-widget tr td span:hover
    background-color: white
</style>
