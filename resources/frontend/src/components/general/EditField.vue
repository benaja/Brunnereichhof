<template>
  <div class="px-1">
    <p v-if="label" class="label grey--text text--darken-3">{{ label }}</p>
    <v-text-field
      v-model="text"
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
      @input="$emit('input', text)"
      @change="$emit('change')"
      @focus="outline = true"
      @blur="outline = false"
    ></v-text-field>
  </div>
</template>

<script>
export default {
  name: 'EditField',
  props: {
    value: {
      default: '',
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
      outline: false,
      text: this.value
    }
  },
  computed: {
    backgroundColor() {
      if (this.disabled || this.readonly) return 'transparent'
      if (this.outline) return 'transparent'
      if (this.text) return 'transparent'
      return 'grey lighten-4'
    },
    classNames() {
      return this.class
    }
  },
  watch: {
    value() {
      this.text = this.value
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

.label {
  margin: 0;
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
