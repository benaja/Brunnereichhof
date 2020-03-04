<template>
  <v-row>
    <v-col cols="12" md="6">
      <v-text-field label="Strasse + Nr*" v-model="value.street" :rules="[rules.required]"></v-text-field>
    </v-col>
    <v-col cols="12" md="6">
      <v-text-field label="Zusatz" v-model="value.addition"></v-text-field>
    </v-col>
    <v-col cols="12" md="6">
      <v-text-field type="number" label="PLZ*" v-model="value.plz" :rules="[rules.required]"></v-text-field>
    </v-col>
    <v-col cols="12" md="6">
      <v-text-field label="Ort*" v-model="value.place" :rules="[rules.required]"></v-text-field>
    </v-col>
  </v-row>
</template>

<script>
import { rules } from '@/utils'

export default {
  props: {
    value: Object
  },
  data() {
    return {
      rules
    }
  }
}
</script>

<style></style>
