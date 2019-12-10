<template>
  <v-row>
    <v-col cols="12" md="6">
      <date-picker
        v-model="date"
        :type="selectYear ? 'year': 'month'"
        :label="selectYear ? 'Jahr': 'Monat'"
        :color="color"
      ></date-picker>
    </v-col>
    <v-col cols="12" md="6">
      <v-switch v-model="selectYear" label="Ganzes Jahr" :color="color"></v-switch>
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
    value: String,
    color: {
      type: String,
      default: 'primary'
    }
  },
  data() {
    return {
      selectYear: false,
      date: null
    }
  },
  mounted() {
    if (this.value) {
      this.date = this.value.split('/')[1]
      this.submitInput()
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
