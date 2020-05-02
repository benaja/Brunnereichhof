<template>
  <div class="container">
    <h1 class="text-center">
      Kunde auswählen
    </h1>
    <v-row
      justify="center"
      class="mt-4"
    >
      <v-col
        cols="12"
        md="6"
      >
        <v-autocomplete
          v-if="$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
          v-model="selectedCustomer"
          label="Kunde Hinzufügen"
          append-outer-icon="search"
          :items="customers"
          item-value="id"
          item-text="name"
          @input="addCustomer"
        ></v-autocomplete>
        <v-list class="pa-0 elevation-1">
          <template v-for="(rapport, index) of rapports">
            <div :key="index">
              <v-divider v-if="index != 0"></v-divider>
              <v-list-item
                :to="'/rapport/' + rapport.id"
                color="primary"
              >
                <v-list-item-content>
                  {{ rapport.customer.firstname }} {{ rapport.customer.lastname }}
                </v-list-item-content>
                <v-list-item-avatar>
                  <v-icon
                    v-if="rapport.isFinished"
                    color="primary"
                  >
                    check_circle
                  </v-icon>
                </v-list-item-avatar>
              </v-list-item>
            </div>
          </template>
        </v-list>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  name: 'RapportWeek',
  components: {},
  data() {
    return {
      rapports: [],
      customers: [],
      selectedCustomer: null
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios
      .get(`/rapport/week/${this.$route.params.week}`)
      .then(response => {
        this.rapports = response.data
        this.$store.commit('isLoading', false)
      })
      .catch(() => {
        this.$store.commit('isLoading', false)
        this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
      })

    this.$store.dispatch('customers').then(customers => {
      this.customers = customers
    })
  },
  methods: {
    addCustomer() {
      if (this.selectedCustomer) {
        this.axios
          .post('rapports', {
            week: this.$route.params.week,
            customer_id: this.selectedCustomer
          })
          .then(response => {
            this.$router.push(`/rapport/${response.data.id}`)
          })
          .catch(() => {
            this.$swal('Erstellen Fehlgeschlagen', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
