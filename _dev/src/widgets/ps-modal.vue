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
  <div class="modal fade" id="ps-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" :class="{'modal-lg': size === 'lg', 'modal-sm': size === 'sm', 'modal-xl': size === 'xl'}"
         role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">
            {{ translations.modal_title }}
          </h4>
          <button type="button" class="close" @click="hideModal">
            <i class="material-icons">close</i>
          </button>
        </div>
        <div class="modal-body">
          <slot/>
        </div>
        <div class="modal-footer">
          <PsSpinner v-show="isLoading"/>
          <div class="actions" v-if="translations.button_save || translations.button_leave">
            <PSButton
                @click.native="onLeave"
                class="btn-lg"
                ghost
                data-dismiss="modal"
                v-if="translations.button_leave"
            >
              {{ translations.button_leave }}
            </PSButton>
            <PSButton
                @click.native="onSave"
                class="btn-lg"
                primary
                data-dismiss="modal"
                v-if="translations.button_save"
            >
              {{ translations.button_save }}
            </PSButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PSButton from "./ps-button.vue";
import {EventBus} from "../utils/event-bus";
import PsSpinner from "./ps-spinner.vue";

export default {
  props: {
    translations: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    size: {
      type: String,
      required: false,
      default: null,
    },
    isLoading: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  mounted() {
    EventBus.on("showModal", () => {
      this.showModal();
    });
    EventBus.on("hideModal", () => {
      this.hideModal();
    });
  },
  methods: {
    showModal() {
      $(this.$el).modal("show");
    },
    hideModal() {
      this.$emit("leave");
      $(this.$el).modal("hide");
    },
    onSave() {
      this.$emit("save");
    },
    onLeave() {
      this.$emit("leave");
    },
  },
  components: {
    PsSpinner,
    PSButton,
  },
};
</script>

<style lang="sass" scoped>
@import '~@scss/config/_settings.scss'

.modal-header
  .close
    font-size: 1.2rem
    color: $gray-medium
    opacity: 1

.modal-footer
  .ps-spinner
    margin: 0
  .actions
    display: flex
    align-items: center
    margin-left: auto
</style>
