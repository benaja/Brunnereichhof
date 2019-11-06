<template>
  <div class="pa-4">
    <h2>Wochenrapport</h2>
    <v-combobox
      v-model="selectedCustomer"
      :items="customers"
      :loading="isLoadingCustomers"
      :search-input.sync="searchStringEmployee"
      no-data-text="keine Daten"
      @focus="searchStringEmployee = ''"
      label="Kunde"
      prepend-icon="search"
      item-text="name"
      item-value="id"
    ></v-combobox>
    <v-menu v-model="menu" transition="scale-transition" offset-y min-width="290px">
      <template v-slot:activator="{ on }">
        <v-text-field v-model="selectedDate" label="Datum" prepend-icon="event" readonly v-on="on"></v-text-field>
      </template>
      <v-date-picker ref="picker" locale="ch-de" v-model="selectedDate"></v-date-picker>
    </v-menu>
    <p>
      <v-btn color="primary" :disabled="!selectedDate" @click="generatePdf">Pdf generieren</v-btn>
    </p>
  </div>
</template>

<script>
export default {
  name: 'CustomerYearRapport',
  data() {
    return {
      customers: [],
      selectedCustomer: null,
      selectedDate: new Date().toISOString().substr(0, 10),
      menu: null,
      isLoadingCustomers: false,
      customersLoaded: false,
      searchStringEmployee: null
    }
  },
  methods: {
    generatePdf() {
      this.axios.get('pdftoken').then(response => {
        window.location = `${process.env.VUE_APP_API_URL}pdf/customer/week/${this.selectedDate}?token=${response.data}&customer_id=${
          this.selectedCustomer ? this.selectedCustomer.id : 0
        }`
      })
    }
  },
  watch: {
    searchStringEmployee() {
      if (this.customersLoaded) return
      if (this.isLoadingCustomers) return
      this.isLoadingCustomers = true

      this.$store.dispatch('customers').then(customers => {
        this.customers = customers
        this.customers.unshift({
          id: 0,
          name: 'Alle'
        })
        this.isLoadingCustomers = false
        this.customersLoaded = true
      })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
