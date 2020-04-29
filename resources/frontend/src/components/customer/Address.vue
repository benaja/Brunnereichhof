<template>
  <v-row>
    <v-col
      cols="12"
      md="6"
    >
      <v-text-field
        v-model="value.street"
        label="Strasse + Nr*"
        :rules="[rules.required]"
      ></v-text-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
    >
      <v-text-field
        v-model="value.addition"
        label="Zusatz"
      ></v-text-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
    >
      <v-text-field
        v-model="value.plz"
        type="number"
        label="PLZ*"
        :rules="[rules.required]"
      ></v-text-field>
    </v-col>
    <v-col
      cols="12"
      md="6"
    >
      <v-text-field
        v-model="value.place"
        label="Ort*"
        :rules="[rules.required]"
      ></v-text-field>
    </v-col>
  </v-row>
</template>

<script>
import { rules } from '@/utils'

export default {
  props: {
    value: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      rules
    }
  }
}
</script>
