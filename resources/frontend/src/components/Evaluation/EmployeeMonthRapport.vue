<template>
  <div class="pa-4">
    <h2>Monatsrapport</h2>
    <v-menu transition="scale-transition" offset-y min-width="290px">
      <template v-slot:activator="{ on }">
        <v-text-field v-model="selectedDate" label="Monat" prepend-icon="event" readonly v-on="on"></v-text-field>
      </template>
      <v-date-picker v-model="selectedDate" type="month" locale="ch-de"></v-date-picker>
    </v-menu>
    <p>
      <v-btn color="primary" :disabled="!selectedDate" @click="generatePdf">Pdf generieren</v-btn>
    </p>
  </div>
</template>

<script>
export default {
  name: 'HourRecordsWorker',
  data() {
    return {
      selectedDate: null
    }
  },
  methods: {
    generatePdf() {
      this.axios.get(process.env.VUE_APP_API_URL + 'pdftoken').then(response => {
        window.location = `${process.env.VUE_APP_API_URL}pdf/employee/month/${this.selectedDate}?token=${response.data}`
      })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
