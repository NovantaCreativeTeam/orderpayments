<template>
  <ValidationProvider ref="provider" :rules="validationRules" :vid="id" :name="name ?? label"
                      v-slot="{ errors, passed, failed, validate }">
    <div class="form-group"
         :class="{'row': horizontal, 'has-success': passed && validationRules != null, 'has-danger': failed}">
      <label class="form-control-label" :for="id" :class="{'col-sm-3': horizontal}">{{ label }}</label>
      <div :class="{ 'col-sm-9': horizontal }">
        <div class="tags-input search-input search d-flex flex-wrap" @click="focus()" :class="{
                            'is-valid': passed && validationRules != null,
                            'is-invalid': failed
                        }">
          <div class="tags-wrapper">
            <span v-for="(tag, index) in value" :key="index" class="tag">{{ tag }}<i class="material-icons"
                                                                                     @click="close(index)">close</i></span>
          </div>
          <input ref="tags" type="text" :id="id" class="form-control input" :disabled="disabled"
                 @input="validate($event)"
                 @keydown.enter="passed && add($event.currentTarget.value)"
                 @keydown.delete.stop="remove()"
                 @focusout="focusOut($event.currentTarget.value)"
          />
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
    value: String | Array,
    validationRules: {
      type: String | Object,
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
      tag: null
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
      var tags = this.value
      tags.push(tag.trim());
      this.reset();
      this.$emit('input', tags);
      this.$emit('add', tag)
    },
    close(index) {
      var tags = this.value
      var removedTag = tags[index]
      tags.splice(index, 1)
      validate(tags, 'required').then((result) => {
        this.$refs.provider.setErrors(result.errors)
      })
      this.$emit('input', tags)
      this.$emit('remove', removedTag)
    },
    remove() {
      if (this.value.length && this.tag && !this.tag.length) {
        var tags = this.value
        var removedTag = tags.pop();
        this.$emit('input', tags);
        this.$emit('remove', removedTag);
      }
    },
    focusOut(tag) {
      if (tag != null && tag !== "") {
        var tags = this.value
        tags.push(tag.trim());
        this.reset();
        this.$emit('input', tags);
        this.$emit('add', tag)
      }
    }
  },
  components: {
    ValidationProvider
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
