<template>
  <v-row>
    <v-col cols="12" :md="long ? 2 : 4" class="py-0">
      <p class="description font-weight-bold subheading mb-0">{{label}}</p>
    </v-col>
    <v-col cols="12" :md="long ? 10 : 8" class="py-0">
      <edit-field
        :value="value"
        @input="v => $emit('input', v)"
        @change="$emit('change')"
        :readonly="readonly"
        :type="type"
      ></edit-field>
    </v-col>
  </v-row>
</template>

<script>
export default {
  props: {
    label: String,
    value: [String, Number],
    readonly: Boolean,
    type: {
      default: 'text',
      type: String
    },
    long: Boolean
  }
}
</script>

<style lang="scss" scoped>
.description {
  margin-top: 12px;
}
</style>
