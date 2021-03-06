<template>
  <fragment>
    <navigation-bar title="Hofmitarbeiter"></navigation-bar>
    <v-container>
      <search-bar
        ref="searchBar"
        v-model="workersFiltered"
        :items="allWorkers"
        name="workers"
        label="Hofmitarbeiter suchen"
        :custom-filter-function="filterActive"
        @showDeleted="s => showDeleted = s"
      >
        <v-switch
          slot="custom-filter"
          v-model="showActive"
          label="Aktiv"
          :disabled="showDeleted"
        ></v-switch>
      </search-bar>
      <progress-linear :loading="$store.getters.isLoading.workers"></progress-linear>
      <v-expansion-panels>
        <v-expansion-panel
          v-for="worker in workersFiltered"
          :key="worker.id"
        >
          <v-expansion-panel-header
            hide-actions
            @click="loadWorker(worker.id)"
          >
            <p class="pt-2 mt-1">
              <v-icon class="account-icon">
                account_circle
              </v-icon>
              <span class="font-weight-bold pl-2">{{ worker.name }}</span>
            </p>
            <v-btn
              v-if="showDeleted"
              color="primary"
              max-width="200"
              depressed
              @click="$refs.searchBar.restoreItem(worker)"
            >
              Wiederherstellen
            </v-btn>
            <v-btn
              v-else
              color="primary"
              max-width="100"
              depressed
              :to="'/worker/' + worker.id"
            >
              Details
            </v-btn>
          </v-expansion-panel-header>
          <v-expansion-panel-content>
            <v-row v-if="activeWorker && activeWorker.id === worker.id">
              <v-col
                cols="12"
                md="6"
                lg="4"
              >
                <h4>Aktueller Monat</h4>
                <p class="mb-0">
                  Arbeitsstunden: {{ activeWorker.workHoursThisMonth }}h
                </p>
                <p class="mb-0">
                  Frühstück: {{ activeWorker.mealsThisMonth.breakfast }}
                </p>
                <p class="mb-0">
                  Mittagessen: {{ activeWorker.mealsThisMonth.lunch }}
                </p>
                <p>Abendessen: {{ activeWorker.mealsThisMonth.dinner }}</p>
              </v-col>
              <v-col
                cols="12"
                md="6"
                lg="4"
              >
                <h4>Vergangener Monat</h4>
                <p class="mb-0">
                  Arbeitsstunden: {{ activeWorker.workHoursLastMonth }}h
                </p>
                <p class="mb-0">
                  Frühstück: {{ activeWorker.mealsLastMonth.breakfast }}
                </p>
                <p class="mb-0">
                  Mittagessen: {{ activeWorker.mealsLastMonth.lunch }}
                </p>
                <p>Abendessen: {{ activeWorker.mealsLastMonth.dinner }}</p>
              </v-col>
              <v-col
                cols="12"
                md="6"
                lg="3"
              >
                <h4>Ferien dieses Jahr</h4>
                <p class="mb-0">Geplant: {{ activeWorker.holidaysPlant }} Tage</p>
                <p>Bezogen: {{ activeWorker.holidaysDone }} Tage</p>
              </v-col>
              <v-col
                cols="12"
                md="6"
                lg="1"
              >
                <v-switch
                  v-if="!showDeleted"
                  v-model="worker.isActive"
                  class="float-right mr-4 pr-3"
                  label="Aktiv"
                  :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
                  @change="update(worker)"
                ></v-switch>
              </v-col>
            </v-row>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['worker_write'])"
        to="/worker/add"
        fixed
        bottom
        right
        fab
        color="primary"
      >
        <v-icon>add</v-icon>
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import SearchBar from '@/components/general/SearchBar'
import { mapGetters } from 'vuex'

export default {
  name: 'Workers',
  components: {
    SearchBar
  },
  data() {
    return {
      workersFiltered: [],
      showDeleted: false,
      showActive: true,
      activeWorker: null
    }
  },
  computed: {
    ...mapGetters(['allWorkers'])
  },
  mounted() {
    this.$store.dispatch('fetchWorkers', { withDetails: true })
  },
  methods: {
    filterActive(worker) {
      return (worker.isActive && this.showActive) || (!worker.isActive && !this.showActive)
    },
    update(worker) {
      this.axios.patch(`/workers/${worker.id}`, { isActive: worker.isActive }).catch(() => {
        this.$swal('Fehler', 'Aktion konnte nicht durchgeführt werden.', 'error')
      }).then(() => {
        this.$store.dispatch('alert', { text: `Hofmitarbeiter erfolgreich auf ${worker.isActive ? 'aktiv' : 'inaktiv'} gesetzt.` })
      })
    },
    loadWorker(workerId) {
      if (this.activeWorker && this.activeWorker.id === workerId) {
        return
      }

      this.axios.get(`/workers/${workerId}`).then(response => {
        this.activeWorker = response.data
      }).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Laden der Details')
      })
    }
  }
}
</script>

<style lang="scss" scoped>
#addbutton {
  position: fixed;
  bottom: 50px;
  right: 50px;
}

.switch {
  margin-top: 1em;
}

.filter {
  text-align: right;
}
</style>
