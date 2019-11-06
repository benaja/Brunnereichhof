<template>
  <div class="pa-4">
    <h2>Jahresrapport</h2>
    <v-combobox
      v-model="selectedEmployee"
      :items="employees"
      label="Mitarbeiter"
      prepend-icon="search"
      item-text="name"
      item-value="id"
    ></v-combobox>
    <v-menu v-model="menu" transition="scale-transition" offset-y min-width="290px">
      <template v-slot:activator="{ on }">
        <v-text-field v-model="selectedDate" label="Jahr" prepend-icon="event" readonly v-on="on"></v-text-field>
      </template>
      <v-date-picker
        ref="picker"
        locale="ch-de"
        :max="new Date().toISOString().substr(0, 10)"
        min="1950-01-01"
      ></v-date-picker>
    </v-menu>
    <p>
      <v-btn
        color="primary"
        :disabled="!selectedEmployee || !selectedDate"
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
      selectedDate: null,
      menu: null
    }
  },
  mounted() {
    this.$store.dispatch('employees').then(employees => (this.employees = employees))
  },
  methods: {
    generatePdf() {
      this.axios.get(process.env.VUE_APP_API_URL + 'pdftoken').then(response => {
        window.location = `${process.env.VUE_APP_API_URL}pdf/employee/year/${this.selectedDate}?token=${response.data}&employee_id=${this.selectedEmployee.id}`
      })
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

<style lang="scss" scoped>
</style>
