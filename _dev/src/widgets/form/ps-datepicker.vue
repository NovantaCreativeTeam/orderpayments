<template>
    <ValidationProvider :rules="validationRules" :vid="id" :name="name ?? label" v-slot="{ errors, passed, failed }">
        <div class="form-group" :class="{'row': horizontal, 'has-success': passed && validationRules != null, 'has-danger': failed}">
            <label v-if="label" class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{label}}</label>
            <div :class="{ 'col-sm-9': horizontal }">
                <div class="input-group date">
                    <input type="text" ref="datepicker" class="form-control" :disabled="disabled" :id="id" v-model="dateValue"
                        :class="{
                            'is-valid': passed && validationRules != null,
                            'is-invalid': failed,
                        }"/>
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="material-icons">event</i>
                        </span>
                    </div>
                </div>

                <small class="form-text" v-if="helper">{{ helper }}</small>
                <div class="invalid-feedback" v-show="errors.length && failed">
                    <span v-for="error in errors" :key="error">{{error}}</span>
                </div>
            </div>
        </div>
    </ValidationProvider>
</template>

<script>
import { ValidationProvider, validate } from "vee-validate";
import moment from 'moment'
import 'moment/min/locales'

export default {
    props: {
        id: String,
        name: String,
        value: String | Number,
        disabled: {
            type: Boolean,
            default: false,
        },
        validationRules: {
            type: String | Object,
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
        let date = this.value;
        // Se il valore è una stringa, proviamo a interpretarla come ISO o Date string standard
        // moment(undefined) o moment(null) ritorna un Invalid Date object (non letteralmente null)
        let initialDate = date ? moment(date) : null;

        return {
            dateValue: (initialDate && initialDate.isValid()) ? initialDate.format('DD/MM/YYYY') : null
        }
    },
    watch: {
        value(newVal) {
            let initialDate = newVal ? moment(newVal) : null;
            if (initialDate && initialDate.isValid()) {
                let formatted = initialDate.format('DD/MM/YYYY');
                if (this.dateValue !== formatted) {
                    this.dateValue = formatted;
                    $(this.$refs.datepicker).data('DateTimePicker').date(formatted);
                }
            } else if (!newVal) {
                this.dateValue = null;
                $(this.$refs.datepicker).data('DateTimePicker').date(null);
            }
        }
    },
    mounted() {
        $(this.$refs.datepicker).datetimepicker({
            format: 'DD/MM/YYYY',
            date: this.dateValue,
            locale: window.full_language_code
        }).on('dp.change', (infos) => {
            var current_date = infos.date ? infos.date.format('DD/MM/YYYY') : ""
            if(this.dateValue != current_date)
            {
                this.dateValue = current_date
                validate(this.dateValue)
                this.$emit('input', current_date)
            }
        });
    },
    components: {
        ValidationProvider
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
