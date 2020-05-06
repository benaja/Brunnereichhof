<template>
  <base-input
    :restore-message="computedRestoreMessage"
    :label="label"
    @restore="restoreOriginal"
  >
    <v-select
      :value="value"
      :items="items"
      solo
      text
      :item-text="itemText"
      :item-value="itemValue"
      :disabled="disabled"
      :background-color="backgroundColor"
      :append-icon="disabled ? '' : 'arrow_drop_down'"
      :chips="chips"
      :multiple="multiple"
      deletable-chips
      :rules="rules"
      :readonly="readonly"
      @change="$emit('change')"
      @input="$emit('input', $event)"
    />
  </base-input>
</template>

<script>
import BaseInput from './_BaseInput'
import InputMixin from './_InputMixin'

export default {
  components: {
    BaseInput
  },
  mixins: [InputMixin],
  props: {
    value: {
      type: [String, Array, Number],
      default: ''
    },
    items: {
      type: Array,
      default: () => []
    },
    disabled: {
      type: Boolean,
      default: false
    },
    label: {
      type: String,
      default: null
    },
    itemText: {
      type: String,
      default: 'text'
    },
    itemValue: {
      type: String,
      default: 'value'
    },
    chips: {
      type: Boolean,
      default: false
    },
    multiple: {
      type: Boolean,
      default: false
    },
    rules: {
      default: () => [],
      type: Array
    },
    readonly: {
      default: false,
      type: Boolean
    }
  },
  data() {
    return {
      outline: false
    }
  },
  computed: {
    backgroundColor() {
      if (this.disabled || this.readonly || this.outline || this.value) return 'transparent'
      return 'grey lighten-4'
    }
  }
}
</script>

<style lang="scss">
.custom-input .v-select__selection--disabled {
  color: black !important;
}

.custom-input .v-input__slot {
  box-shadow: none !important;
}
</style>
