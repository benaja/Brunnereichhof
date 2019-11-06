<template>
  <v-menu
    v-model="model"
    :close-on-content-click="false"
    :nudge-right="40"
    transition="scale-transition"
    offset-y
    min-width="290px"
  >
    <template v-slot:activator="{ on }">
      <v-text-field
        :value="formatedDate"
        :label="label"
        prepend-icon="event"
        readonly
        :rules="rules"
        :color="color"
        v-on="on"
      ></v-text-field>
    </template>
    <v-date-picker
      v-model="date"
      @input="model = false"
      locale="ch-de"
      :color="color"
      first-day-of-week="1"
    ></v-date-picker>
  </v-menu>
</template>

<script>
import moment from 'moment'

export default {
  name: 'DatePicker',
  props: {
    value: String,
    label: String,
    rules: {
      type: Array,
      default: () => []
    },
    color: {
      type: String,
      default: 'primary'
    }
  },
  data() {
    return {
      model: false
    }
  },
  computed: {
    formatedDate() {
      return this.date ? moment(this.date).format('DD.MM.YYYY') : ''
    },
    date: {
      get: function() {
        return this.value
      },
      set: function(value) {
        this.$emit('input', value)
      }
    }
  }
}
</script>

<style>
</style>
