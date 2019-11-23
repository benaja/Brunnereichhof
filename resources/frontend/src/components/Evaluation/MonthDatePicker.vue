<template>
  <v-row>
    <v-col cols="12" md="6">
      <date-picker
        v-model="date"
        :type="selectYear ? 'year': 'month'"
        :label="selectYear ? 'Jahr': 'Monat'"
      ></date-picker>
    </v-col>
    <v-col cols="12" md="6">
      <v-switch v-model="selectYear" label="Ganzes Jahr"></v-switch>
    </v-col>
  </v-row>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'

export default {
  components: {
    DatePicker
  },
  props: {
    value: String
  },
  data() {
    return {
      selectYear: false,
      date: null
    }
  },
  methods: {
    submitInput() {
      this.$emit('input', `${this.selectYear ? 'year' : 'month'}/${this.date}`)
    }
  },
  watch: {
    selectYear() {
      this.submitInput()
    },
    date() {
      this.submitInput()
    }
  }
}
</script>

<style>
</style>
