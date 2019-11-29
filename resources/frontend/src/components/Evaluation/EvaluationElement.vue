<template>
  <div class="pa-4">
    <h2>{{evaluation.title}}</h2>
    <div v-for="(inputField, index) of evaluation.inputFields" :key="index">
      <date-picker
        v-if="isDatePicker(inputField)"
        v-model="inputField.value"
        :type="datePickerType(inputField)"
        :label="datePickerLabel(inputField)"
      ></date-picker>
      <month-date-picker
        v-if="inputField.type === EVALUATION_INPUT_TYPES.MONTH_OR_YEAR_PICKER"
        v-model="inputField.value"
      ></month-date-picker>
      <evaluation-input v-else :input-field="inputField"></evaluation-input>
    </div>
    <p>
      <v-btn
        color="primary"
        :disabled="!allValid"
        @click="generatePdf"
      >{{evaluation.buttonText || 'pdf erstellen'}}</v-btn>
    </p>
  </div>
</template>

<script>
import EvaluationInput from '@/components/Evaluation/EvaluationInput'
import { EVALUATION_INPUT_TYPES } from '@/constants'
import DatePicker from '@/components/general/DatePicker'
import MonthDatePicker from '@/components/Evaluation/MonthDatePicker'

export default {
  components: {
    EvaluationInput,
    DatePicker,
    MonthDatePicker
  },
  props: {
    evaluation: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      datePickerModel: false
    }
  },
  computed: {
    EVALUATION_INPUT_TYPES() {
      return EVALUATION_INPUT_TYPES
    },
    allValid() {
      let allValid = true
      for (let key in this.evaluation.rules) {
        let value = this.getValue(key)
        if (!this.evaluation.rules[key](value)) allValid = false
      }
      return allValid
    }
  },
  methods: {
    generatePdf() {
      this.axios.get('pdftoken').then(response => {
        let regex = /^([^{]*)\{([^{]+)\}(.*)$/
        let url = this.evaluation.url
        let pdfUrl = ''
        do {
          let matches = regex.exec(url)
          if (!matches) {
            pdfUrl = url
            url = null
          } else {
            pdfUrl += matches[1] + this.getValue(matches[2])
            url = matches[3]
          }
        } while (url)
        pdfUrl = `${process.env.VUE_APP_API_URL}${pdfUrl}`
        if (this.evaluation.url.includes('?')) pdfUrl += `&token=${response.data}`
        else pdfUrl += `?token=${response.data}`
        window.location = pdfUrl
      })
    },
    getValue(key) {
      let inputField = this.evaluation.inputFields.find(i => i.key === key)
      if (inputField.selectAll && !inputField.value) return 'all'
      if (!inputField.value) return null
      return inputField.value.id || inputField.value
    },
    isDatePicker(inputField) {
      return (
        inputField.type === EVALUATION_INPUT_TYPES.MONTH_PICKER ||
        inputField.type === EVALUATION_INPUT_TYPES.DATE_PICKER ||
        inputField.type === EVALUATION_INPUT_TYPES.YEAR_PICKER
      )
    },
    datePickerType(inputField) {
      if (inputField.type === EVALUATION_INPUT_TYPES.MONTH_PICKER) return 'month'
      else if (inputField.type === EVALUATION_INPUT_TYPES.DATE_PICKER) return 'date'
      else return 'year'
    },
    datePickerLabel(inputField) {
      if (this.datePickerType(inputField) === 'month') return 'Monat'
      else if (this.datePickerType(inputField) === 'date') return 'Datum'
      else return 'Jahr'
    }
  },
  watch: {
    menu(val) {
      val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
      if (!val) {
        this.selectedDate = this.$refs.picker.inputYear
      }
    }
  }
}
</script>

<style>
</style>
