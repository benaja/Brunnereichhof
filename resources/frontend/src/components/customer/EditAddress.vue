<template>
  <v-row>
    <v-col cols="12" md="6" class="py-0">
      <input-field
        label="Strasse + Nr"
        v-model="value.street"
        @input="$emit('change')"
        :readonly="readonly"
      ></input-field>
    </v-col>
    <v-col cols="12" md="6" class="py-0">
      <input-field
        label="Zusatz"
        v-model="value.addition"
        @input="$emit('change')"
        :readonly="readonly"
      ></input-field>
    </v-col>
    <v-col cols="12" md="6" class="py-0">
      <input-field label="PLZ" v-model="value.plz" @input="$emit('change')" :readonly="readonly"></input-field>
    </v-col>
    <v-col cols="12" md="6" class="py-0">
      <input-field label="Ort" v-model="value.place" @input="$emit('change')" :readonly="readonly"></input-field>
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
    value: Object,
    readonly: Boolean
  },
  methods: {
    test() {
      console.log('test')
    }
  }
}
</script>

<style></style>
