<template>
  <fragment>
    <navigation-bar
      title="Kunde auswählen"
      :loading="isLoading"
    ></navigation-bar>
    <v-container>
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
    </v-container>
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  data() {
    return {
      rapports: [],
      selectedCustomer: null,
      isLoading: false
    }
  },
  computed: {
    ...mapGetters(['customers'])
  },
  mounted() {
    this.isLoading = true
    this.axios
      .get(`/rapports/week/${this.$route.params.week}`)
      .then(response => {
        this.rapports = response.data
      })
      .catch(() => {
        this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
      }).finally(() => {
        this.isLoading = false
      })
    this.$store.dispatch('fetchCustomers')
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
