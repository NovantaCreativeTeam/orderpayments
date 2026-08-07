<template>
  <Field :rules="validationRules" :name="name ?? label" v-model="internalValue" v-slot="{ errors, meta }">
    <div class="form-group"
         :class="{'row': horizontal, 'has-success': meta.valid && validationRules != null, 'has-danger': meta.touched && !meta.valid}">
      <label class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{ label }}</label>
      <div :class="{ 'col-sm-9': horizontal }">
        <div class="tags-input search-input search d-flex flex-wrap" @click="focus()" :class="{
                            'is-valid': meta.valid && validationRules != null,
                            'is-invalid': meta.touched && !meta.valid
                        }">
          <div class="tags-wrapper">
            <span v-for="(tag, index) in internalValue" :key="index" class="tag">{{ tag }}<i class="material-icons"
                                                                                     @click="close(index)">close</i></span>
          </div>
          <input ref="tags" type="text" :id="id" class="form-control input" :disabled="disabled"
                 @input="meta.validate"
                 @keydown.enter="meta.valid && add($event.currentTarget.value)"
                 @keydown.delete.stop="remove()"
                 @focusout="focusOut($event.currentTarget.value)"
          />
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
    modelValue: [String, Array],
    validationRules: {
      type: [String, Object],
      required: false
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
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      tag: null,
      internalValue: this.modelValue || []
    }
  },
  watch: {
    modelValue(newVal) {
      this.internalValue = newVal || [];
    },
    internalValue(newVal) {
      this.$emit('update:modelValue', newVal);
    }
  },
  methods: {
    focus() {
      this.$refs.tags.focus();
    },
    reset() {
      this.$refs.tags.value = ''
    },
    add(tag) {
      this.internalValue.push(tag.trim());
      this.reset();
      this.$emit('add', tag)
    },
    close(index) {
      var removedTag = this.internalValue[index]
      this.internalValue.splice(index, 1)
      this.$emit('remove', removedTag)
    },
    remove() {
      if (this.internalValue.length && this.tag && !this.tag.length) {
        var removedTag = this.internalValue.pop();
        this.$emit('remove', removedTag);
      }
    },
    focusOut(tag) {
      if (tag != null && tag !== "") {
        this.internalValue.push(tag.trim());
        this.reset();
        this.$emit('add', tag)
      }
    }
  },
  components: {
    Field
  }
};
</script>

<style lang="sass" scoped>
@import '~@scss/config/_settings.scss'

.form-group
  &.has-success, &.has-danger, &.has-warning
    .tags-input
      .form-control
        background-image: none

  &.has-success
    .tags-input
      &:focus-within
        border-color: $success

  &.has-danger
    .tags-input
      &:focus-within
        border-color: $danger

  &.has-warning
    .tags-input
      &:focus-within
        border-color: $warning
</style>
