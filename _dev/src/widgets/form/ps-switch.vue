<!--**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *-->
<template>
  <div class="form-group" :class="{'row': horizontal}">
    <label v-if="label" class="form-control-label" :for="id" :class="{'col-auto': horizontal}">{{ label }}</label>
    <div :class="{ 'col-md-6': horizontal }">
      <span class="ps-switch">
        <input
          type="radio"
          :id="id + '_off'"
          :disabled="disabled"
          :value="false"
          :checked="model === false"
          @change="$emit('change', false)"
        />
        <label :for="id + '_off'">Off</label>
        <input
          type="radio"
          :id="id + '_on'"
          :value="true"
          :checked="model === true"
          @change="$emit('change', true)"
        />
        <label :for="id + '_on'">On</label>
        <span class="slide-button"></span>
      </span>
      <small class="form-text" v-if="helper">
        {{helper}}
      </small>
    </div>

  </div>
</template>

<script>
export default {
  model: {
    prop: 'model',
    event: 'change',
  },
  props: {
    id: {
      type: String,
      required: false,
      default: "",
    },
    model: {
      type: Boolean,
      required: true,
      default: false,
    },
    label: {
      type: String,
      required: false
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    horizontal: {
      type: Boolean,
      default: false
    },
    helper: String,
  },
  methods: {
    onChange(event) {
      console.log(event);
      this.$emit('change', event.target.checked);
    }
  }
};
</script>
