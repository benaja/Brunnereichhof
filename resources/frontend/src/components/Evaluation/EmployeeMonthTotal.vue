<template>
  <div class="pa-4">
    <h2>Tagestotale</h2>
    <v-combobox
      v-model="selectedEmployee"
      :items="employees"
      label="Mitarbeiter"
      prepend-icon="search"
      item-text="name"
      item-value="id"
    ></v-combobox>
    <v-row>
      <v-col cols="12" md="6">
        <v-menu v-model="menu" transition="scale-transition" offset-y min-width="290px">
          <template v-slot:activator="{ on }">
            <v-text-field
              v-model="selectedDate"
              :label="yearTotal ? 'Jahr' : 'Monat'"
              prepend-icon="event"
              readonly
              v-on="on"
            ></v-text-field>
          </template>
          <v-date-picker
            ref="picker"
            locale="ch-de"
            type="month"
            :max="new Date().toISOString().substr(0, 10)"
            min="1950-01-01"
          ></v-date-picker>
        </v-menu>
      </v-col>
      <v-col cols="12" md="6">
        <v-switch v-model="yearTotal" label="Ganzes Jahr"></v-switch>
      </v-col>
    </v-row>
    <p>
      <v-btn
        color="primary"
        :disabled="!selectedEmployee || !selectedEmployee.id || !selectedDate"
        @click="generatePdf"
      >Pdf generieren</v-btn>
    </p>
  </div>
</template>

<script>
export default {
  name: 'EmployeeYearRapport',
  data() {
    return {
      employees: [],
      selectedEmployee: null,
      selectedDate: new Date().toISOString().substr(0, 7),
      menu: null,
      yearTotal: false
    }
  },
  mounted() {
    this.$store.dispatch('employees').then(employees => (this.employees = employees))
  },
  methods: {
    generatePdf() {
      this.axios.get('pdftoken').then(response => {
        if (this.yearTotal) {
          window.location = `${process.env.VUE_APP_API_URL}employee/${this.selectedEmployee.id}/evaluation/year/${this.selectedDate}
          ?token=${response.data}`
        } else {
          window.location = `${process.env.VUE_APP_API_URL}employee/${this.selectedEmployee.id}/evaluation/month/${this.selectedDate}
          ?token=${response.data}`
        }
      })
    }
  },
  watch: {
    menu(val) {
      if (this.yearTotal) {
        val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
        if (!val) {
          this.selectedDate = this.$refs.picker.inputYear
        }
      } else if (!val) {
        this.selectedDate = this.$refs.picker.inputDate
      }
    },
    yearTotal() {
      if (this.yearTotal) this.selectedDate = new Date().getFullYear()
      else this.selectedDate = new Date().toISOString().substr(0, 7)
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
