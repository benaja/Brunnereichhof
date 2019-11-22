<template>
  <div class="pa-4">
    <h2>{{evaluation.title}}</h2>
    <div v-for="(inputField, index) of evaluation.inputFields" :key="index">
      <v-menu
        v-if="inputField.type === EVALUATION_INPUT_TYPES.MONTH_PICKER || inputField.type === EVALUATION_INPUT_TYPES.DATE_PICKER"
        transition="scale-transition"
        offset-y
        min-width="290px"
      >
        <template v-slot:activator="{ on }">
          <v-text-field
            v-model="inputField.value"
            label="Monat"
            prepend-icon="event"
            readonly
            v-on="on"
          ></v-text-field>
        </template>
        <v-date-picker
          v-model="inputField.value"
          :type="inputField.type === EVALUATION_INPUT_TYPES.MONTH_PICKER ? 'month' : 'day'"
          locale="ch-de"
        ></v-date-picker>
      </v-menu>
      <evaluation-input v-else :input-field="inputField"></evaluation-input>
    </div>
    <p>
      <v-btn color="primary" :disabled="!selectedDate" @click="generatePdf">Pdf generieren</v-btn>
    </p>
  </div>
</template>

<script>
import EvaluationInput from '@/components/Evaluation/EvaluationInput'
import { EVALUATION_INPUT_TYPES } from '@/constants'

export default {
  components: {
    EvaluationInput
  },
  props: {
    evaluation: {
      type: Object,
      required: true
    }
  },
  computed: {
    EVALUATION_INPUT_TYPES() {
      return EVALUATION_INPUT_TYPES
    }
  },
  methods: {
    generatePdf() {
      this.axios.get('pdftoken').then(response => {
        let regex = /^([^{]*)\{([^{]+)\}(.*)$/
        let url = this.evaluation.url
        let newUrl = ''
        do {
          let matches = regex.exec(url)
          newUrl += matches[1] + this.getValue(matches[2])
          url = matches[3]
        } while (url)
        console.log(newUrl)
        // window.location = `${process.env.VUE_APP_API_URL}pdf/customer/week/${this.selectedDate}?token=${response.data}&customer_id=${
        //   this.selectedCustomer ? this.selectedCustomer.id : 0
        // }`
      })
    },
    getValue(key) {
      let inputField = this.evaluation.inputFields.find(i => i.key === key)
      return inputField.value
    }
  }
}
</script>

<style>
</style>
