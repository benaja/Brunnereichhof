<template>
  <div>
    <h2>
      Auswertung
      <router-link
        tag="a"
        to="/roomdispositioner/evaluation"
        class="float-right subtitle-1 blue--text pt-1"
      >mehr...</router-link>
    </h2>
    <v-divider class="mb-2"></v-divider>
    <select-employee v-model="selectedEmployee" :select-all="true"></select-employee>
    <!-- <v-autocomplete
      color="blue"
      v-model="selectedEmployee"
      label="Mitarbeiter Auswählen"
      :items="employees"
      item-value="id"
      item-text="name"
      :loading="isLoading"
      :search-input.sync="search"
      @focus="search = ''"
    ></v-autocomplete>-->
    <date-picker v-model="date" label="Zeitpunkt" color="blue"></date-picker>
    <p class="text-center">
      <v-btn text @click="generatePdf" color="blue">PDF generieren</v-btn>
    </p>
  </div>
</template>

<script>
import moment from 'moment'
import DatePicker from '@/components/general/DatePicker'
import SelectEmployee from '@/components/Roomdispositioner/SelectEmployee'
import { downloadFile } from '@/utils'

export default {
  name: 'CreatePdf',
  components: {
    DatePicker,
    SelectEmployee
  },
  data() {
    return {
      employees: [],
      search: null,
      isLoading: false,
      inventarsLoaded: false,
      selectedEmployee: null,
      date: moment().format('YYYY-MM-DD')
    }
  },
  methods: {
    generatePdf() {
      downloadFile(`pdf/reservation/employee/${this.selectedEmployee || 0}?date=${this.date}`)
    }
  },
  watch: {
    search() {
      if (this.inventarsLoaded) return
      if (this.isLoading) return

      this.isLoading = true

      this.$store.dispatch('employees').then(employees => {
        this.employees = employees
        this.employees.unshift({
          id: 0,
          name: 'Alle'
        })
        this.isLoading = false
        this.inventarsLoaded = true
      })
    }
  }
}
</script>

<style>
</style>
