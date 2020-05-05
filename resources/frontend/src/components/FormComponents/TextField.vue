<template>
  <base-input
    :restore-message="computedRestoreMessage"
    :label="label"
    @restore="restoreOriginal"
  >
    <v-text-field
      :value="value"
      :class="['no-label', classes, {'fix-height': !outline}]"
      class="edit-field"
      solo
      text
      :type="type"
      :outlined="outline"
      :color="color"
      :background-color="backgroundColor"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :rules="rules"
      validate-on-blur
      @input="$emit('input', $event)"
      @change="$emit('change')"
      @focus="outline = true"
      @blur="outline = false"
    ></v-text-field>
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
      default: null,
      type: [String, Number]
    },
    type: {
      default: 'text',
      type: String
    },
    color: {
      type: String,
      default: 'primary'
    },
    placeholder: {
      default: '',
      type: String
    },
    label: {
      default: null,
      type: String
    },
    classes: {
      default: '',
      type: String
    },
    disabled: {
      default: false,
      type: Boolean
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

<style lang="scss" scoped>
.no-label {
  margin-right: 5px;
}
.fix-height {
  padding: 4px 0px !important;
}
</style>

<style lang="scss">
.no-label input {
  margin: 0 !important;
  color: black !important;
}

.edit-field .v-input__slot {
  box-shadow: none !important;
}
</style>
