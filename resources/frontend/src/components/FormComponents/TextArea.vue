<template>
  <base-input
    :restore-message="computedRestoreMessage"
    :label="label"
    @restore="restoreOriginal"
  >
    <v-textarea
      :value="value"
      :class="['no-label', classes, outline ? 'outlined' : 'not-outlined']"
      class="edit-field text-area"
      solo
      text
      :rows="rows"
      auto-grow
      :type="type"
      :outlined="outline"
      :color="color"
      :background-color="backgroundColor"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :rules="rules"
      validate-on-blur
      :hide-details="hideDetails"
      @input="input"
      @focus="outline = true"
      @blur="outline = false"
    ></v-textarea>
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
    },
    rows: {
      type: Number,
      default: 2
    },
    hideDetails: {
      type: Boolean,
      default: false
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
</style>

<style lang="scss">
.no-label input {
  margin: 0 !important;
  color: black !important;
}

.edit-field .v-input__slot {
  box-shadow: none !important;
}

.text-area {
  textarea {
    margin-bottom: 10px;
  }

  &.outlined {
   textarea {
     margin: 12px 0 !important;
   }
  }

  &.not-outlined {
    // padding-top: 2px  !important;
    padding: 2px 0px !important;
  }
}

</style>
