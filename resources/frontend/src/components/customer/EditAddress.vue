<template>
  <v-row>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <text-field
        v-model="value.street"
        :original="original.street"
        label="Strasse + Nr"
        :readonly="readonly"
        @input="$emit('change')"
      ></text-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <text-field
        v-model="value.addition"
        :original="original.addition"
        label="Zusatz"
        :readonly="readonly"
        @input="$emit('change')"
      ></text-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <text-field
        v-model="value.plz"
        :original="original.plz"
        label="PLZ"
        :readonly="readonly"
        @input="$emit('change')"
      ></text-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <text-field
        v-model="value.place"
        :original="original.place"
        label="Ort"
        :readonly="readonly"
        @input="$emit('change')"
      ></text-field>
    </v-col>
  </v-row>
</template>

<script>
import { TextField } from '@/components/FormComponents'

export default {
  components: {
    TextField
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    },
    original: {
      type: Object,
      default: () => ({})
    },
    readonly: Boolean
  }
}
</script>
