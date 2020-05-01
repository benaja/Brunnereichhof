<template>
  <div class="pa-4">
    <h2>{{ evaluation.title }}</h2>
    <div
      v-for="(inputField, index) of evaluation.inputFields"
      :key="index"
    >
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
      <evaluation-input
        v-else
        :input-field="inputField"
      ></evaluation-input>
    </div>
    <p>
      <v-btn
        color="primary"
        depressed
        :disabled="!allValid"
        :loading="isLoading"
        @click="generatePdf"
      >
        <v-icon class="mr-2">
          picture_as_pdf
        </v-icon>
        {{ evaluation.buttonText || 'pdf erstellen' }}
      </v-btn>
    </p>
  </div>
</template>

<script>
import EvaluationInput from '@/components/Evaluation/EvaluationInput'
import { EVALUATION_INPUT_TYPES } from '@/constants'
import DatePicker from '@/components/general/DatePicker'
import MonthDatePicker from '@/components/Evaluation/MonthDatePicker'
import { downloadFile } from '@/utils'

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
      isLoading: false,
      EVALUATION_INPUT_TYPES
    }
  },
  computed: {
    allValid() {
      let allValid = true
      for (const key in this.evaluation.rules) {
        const value = this.getValue(key)
        if (!this.evaluation.rules[key](value)) allValid = false
      }
      return allValid
    }
  },
  watch: {
    menu(val) {
      this.$nextTick(() => { this.$refs.picker.activePicker = 'YEAR' })
      if (!val) {
        this.selectedDate = this.$refs.picker.inputYear
      }
    }
  },
  methods: {
    generatePdf() {
      const regex = /^([^{]*)\{([^{]+)\}(.*)$/
      let { url } = this.evaluation
      let pdfUrl = ''
      do {
        const matches = regex.exec(url)
        if (!matches) {
          pdfUrl = url
          url = null
        } else {
          pdfUrl += matches[1] + this.getValue(matches[2])
          url = matches[3]
        }
      } while (url)
      this.isLoading = true
      downloadFile(pdfUrl).catch(error => {
        if (error.message && error.message.includes('Employee has no entries')) {
          this.$store.dispatch('error', 'Mitarbeiter hat keine Stundenangaben zur gewählten Zeit')
        } else {
          this.$store.dispatch('error', 'Pdf konnte nicht generiert werden')
        }
      }).finally(() => {
        this.isLoading = false
      })
    },
    getValue(key) {
      const inputField = this.evaluation.inputFields.find(i => i.key === key)
      if (inputField.selectAll && !inputField.value) return 'all'
      if (!inputField.value) return null
      return inputField.value.id || inputField.value
    },
    isDatePicker(inputField) {
      return (
        inputField.type === EVALUATION_INPUT_TYPES.MONTH_PICKER
        || inputField.type === EVALUATION_INPUT_TYPES.DATE_PICKER
        || inputField.type === EVALUATION_INPUT_TYPES.YEAR_PICKER
      )
    },
    datePickerType(inputField) {
      if (inputField.type === EVALUATION_INPUT_TYPES.MONTH_PICKER) return 'month'
      if (inputField.type === EVALUATION_INPUT_TYPES.DATE_PICKER) return 'date'
      return 'year'
    },
    datePickerLabel(inputField) {
      if (this.datePickerType(inputField) === 'month') return 'Monat'
      if (this.datePickerType(inputField) === 'date') return 'Datum'
      return 'Jahr'
    }
  }
}
</script>
