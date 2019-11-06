<template>
  <div class="pa-4">
    <h2>Mitarbeiterliste</h2>
    <p>
      <v-btn color="primary" @click="generatePdf">Pdf generieren</v-btn>
    </p>
  </div>
</template>

<script>
export default {
  name: 'EmployeeList',
  methods: {
    generatePdf() {
      this.axios.get(process.env.VUE_APP_API_URL + 'pdftoken').then(response => {
        window.location = `${process.env.VUE_APP_API_URL}pdf/employees?token=${response.data}`
      })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
