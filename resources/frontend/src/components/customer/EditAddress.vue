<template>
  <v-row>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <input-field
        v-model="value.street"
        label="Strasse + Nr"
        :readonly="readonly"
        @input="$emit('change')"
      ></input-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <input-field
        v-model="value.addition"
        label="Zusatz"
        :readonly="readonly"
        @input="$emit('change')"
      ></input-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <input-field
        v-model="value.plz"
        label="PLZ"
        :readonly="readonly"
        @input="$emit('change')"
      ></input-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
      class="py-0"
    >
      <input-field
        v-model="value.place"
        label="Ort"
        :readonly="readonly"
        @input="$emit('change')"
      ></input-field>
    </v-col>
  </v-row>
</template>

<script>
import InputField from '@/components/general/InputField'

export default {
  components: {
    InputField
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    },
    readonly: Boolean
  }
}
</script>
