<template>
  <div class="edit-select px-1 pt-1">
    <p v-if="label" class="mb-0">{{ label }}</p>
    <v-select
      v-model="selectedItem"
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
    />
  </div>
</template>

<script>
export default {
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
  computed: {
    selectedItem: {
      get: function() {
        return this.value
      },
      set: function(value) {
        this.$emit('input', value)
      }
    },
    backgroundColor() {
      if (this.disabled) return 'transparent'
      if (this.selectedItem) return 'transparent'
      return 'grey lighten-4'
    }
  }
}
</script>

<style lang="scss" >
.edit-select .v-select__selection--disabled {
  color: black !important;
}

.edit-select .v-input__slot {
  box-shadow: none !important;
}
</style>
