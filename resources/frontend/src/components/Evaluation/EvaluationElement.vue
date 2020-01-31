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
        :loading="isLoading"
      >{{evaluation.buttonText || 'pdf erstellen'}}</v-btn>
    </p>
  </div>
</template>

<script>
import EvaluationInput from '@/components/Evaluation/EvaluationInput'
import { EVALUATION_INPUT_TYPES } from '@/constants'
import DatePicker from '@/components/general/DatePicker'
import MonthDatePicker from '@/components/Evaluation/MonthDatePicker'
import utils from '@/utils'

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
      datePickerModel: false,
      isLoading: false
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
      this.isLoading = true
      utils.downloadFile(pdfUrl).then(() => {
        this.isLoading = false
      })
      // this.axios.get(pdfUrl, { responseType: 'arraybuffer' }).then(response => {
      //   var newBlob = new Blob([response.data], { type: 'application/pdf' })

      //   // IE doesn't allow using a blob object directly as link href
      //   // instead it is necessary to use msSaveOrOpenBlob
      //   if (window.navigator && window.navigator.msSaveOrOpenBlob) {
      //     window.navigator.msSaveOrOpenBlob(newBlob)
      //     return true
      //   }

      //   // // For other browsers:
      //   // // Create a link pointing to the ObjectURL containing the blob.
      //   const data = window.URL.createObjectURL(newBlob)
      //   var link = document.createElement('a')
      //   link.href = data
      //   link.download = response.headers.pragma // axios only supports less headers so I took this one because it works with this
      //   link.click()
      //   setTimeout(function() {
      //     // For Firefox it is necessary to delay revoking the ObjectURL
      //     window.URL.revokeObjectURL(data)
      //   }, 100)
      // })
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
