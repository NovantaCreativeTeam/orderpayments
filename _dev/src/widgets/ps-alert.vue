<template>
  <div
    class="alert"
    :class="alertClass"
    role="alert"
  >
    <slot>{{ text }}</slot>
    <button
      v-if="dismissible"
      type="button"
      class="close"
      data-dismiss="alert"
      aria-label="Close"
    >
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'PsAlert',
  props: {
    type: {
      type: String,
      default: 'info',
    },
    text: {
      type: String,
      default: '',
    },
    dismissible: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    alertClass() {
      return {
        [`alert-${this.type}`]: true,
        'alert-dismissible': this.dismissible,
        fade: this.dismissible,
        show: this.dismissible,
      };
    },
  },
});
</script>
