<template>
  <v-row>
    <v-col
      cols="12"
      :md="long ? 2 : 4"
      class="py-0"
    >
      <p class="description font-weight-bold subheading mb-0">
        {{ label }}
      </p>
    </v-col>
    <v-col
      cols="12"
      :md="long ? 10 : 8"
      class="py-0"
    >
      <slot>
        <edit-field
          :value="value"
          :readonly="readonly"
          :type="type"
          :rules="rules"
          @input="input"
          @change="change"
        ></edit-field>
      </slot>
    </v-col>
  </v-row>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      default: null
    },
    value: {
      type: [String, Number],
      default: ''
    },
    readonly: Boolean,
    type: {
      default: 'text',
      type: String
    },
    long: Boolean,
    rules: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      hasValueChanged: false
    }
  },
  methods: {
    change(value) {
      if (this.hasValueChanged) {
        this.$emit('change', value)
        this.hasValueChanged = false
      }
    },
    input(value) {
      this.$emit('input', value)
      this.hasValueChanged = true
    }
  }
}
</script>

<style lang="scss" scoped>
.description {
  margin-top: 12px;
}
</style>
