<template>
  <div class="pa-4">
    <h2>Stundenangaben</h2>
    <v-combobox
      v-model="selectedWorker"
      :items="workers"
      label="Hofmitarbeiter"
      prepend-icon="search"
      item-text="name"
      item-value="id"
    ></v-combobox>
    <v-menu transition="scale-transition" offset-y min-width="290px">
      <template v-slot:activator="{ on }">
        <v-text-field
          slot="activator"
          v-model="selectedDate"
          label="Monat"
          prepend-icon="event"
          readonly
          v-on="on"
        ></v-text-field>
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
      selectedDate: null,
      selectedWorker: null,
      workers: []
    }
  },
  mounted() {
    this.axios.get(process.env.VUE_APP_API_URL + 'worker').then(response => {
      this.workers = response.data
      for (let worker of this.workers) {
        worker.name = worker.firstname + ' ' + worker.lastname
      }
      this.workers.unshift({
        name: 'Alle',
        id: ''
      })
    })
  },
  methods: {
    generatePdf() {
      this.axios.get(process.env.VUE_APP_API_URL + 'pdftoken').then(response => {
        window.location = `${process.env.VUE_APP_API_URL}pdf/worker/month/${this.selectedDate}?token=${response.data}&workerId=${
          this.selectedWorker ? this.selectedWorker.id : ''
        }`
      })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
