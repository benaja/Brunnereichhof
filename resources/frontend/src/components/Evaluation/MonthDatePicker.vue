<template>
  <v-row>
    <v-col
      cols="12"
      md="8"
    >
      <date-picker
        v-model="date"
        :type="selectYear ? 'year': 'month'"
        :label="selectYear ? 'Jahr': 'Monat'"
        :color="color"
      ></date-picker>
    </v-col>
    <v-col
      cols="12"
      md="4"
    >
      <v-switch
        v-model="selectYear"
        label="Ganzes Jahr"
        :color="color"
      ></v-switch>
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
    value: {
      type: String,
      default: null
    },
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
  watch: {
    selectYear() {
      this.submitInput()
    },
    date() {
      this.submitInput()
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
      this.$emit('input', `dateRangeType=${this.selectYear ? 'year' : 'month'}&date=${this.date}`)
    }
  }
}
</script>
