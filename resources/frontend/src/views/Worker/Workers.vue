<template>
  <div class="container">
    <h1 class="text-center">Hofmitarbeiter-Übersicht</h1>
    <search-bar
      name="workers"
      label="Hofmitarbeiter suchen"
      v-model="workersFiltered"
      @showDeleted="s => showDeleted = s"
      :custom-filter-function="filterActive"
      ref="searchBar"
    >
      <v-switch v-model="showActive" label="Aktiv" slot="custom-filter" :disabled="showDeleted"></v-switch>
    </search-bar>
    <v-expansion-panels>
      <v-expansion-panel v-for="worker in workersFiltered" :key="worker.id">
        <v-expansion-panel-header hide-actions>
          <p class="pt-2 mt-1">
            <v-icon class="account-icon">account_circle</v-icon>
            <span class="font-weight-bold pl-2">{{worker.name}}</span>
          </p>
          <v-btn
            v-if="showDeleted"
            color="primary"
            max-width="200"
            @click="$refs.searchBar.restoreItem(worker)"
          >Wiederherstellen</v-btn>
          <v-btn v-else color="primary" max-width="100" :to="'/worker/' + worker.id">Details</v-btn>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <v-row>
            <v-col cols="12" md="6" lg="4">
              <h4>Aktueller Monat</h4>
              <p class="mb-0">Arbeitsstunden: {{worker.workHoursThisMonth}}h</p>
              <p class="mb-0">Frühstück: {{worker.mealsThisMonth.breakfast}}</p>
              <p class="mb-0">Mittagessen: {{worker.mealsThisMonth.lunch}}</p>
              <p>Abendessen: {{worker.mealsThisMonth.dinner}}</p>
            </v-col>
            <v-col cols="12" md="6" lg="4">
              <h4>Vergangener Monat</h4>
              <p class="mb-0">Arbeitsstunden: {{worker.workHoursLastMonth}}h</p>
              <p class="mb-0">Frühstück: {{worker.mealsLastMonth.breakfast}}</p>
              <p class="mb-0">Mittagessen: {{worker.mealsLastMonth.lunch}}</p>
              <p>Abendessen: {{worker.mealsLastMonth.dinner}}</p>
            </v-col>
            <v-col cols="12" md="6" lg="3">
              <h4>Ferien dieses Jahr</h4>
              <p>Geplant: {{worker.holidaysPlant}} Tage</p>
              <p>Bezogen: {{worker.holidaysDone}} Tage</p>
            </v-col>
            <v-col cols="12" md="6" lg="1">
              <v-switch
                class="float-right mr-4 pr-3"
                v-model="worker.isActive"
                label="Aktiv"
                @change="update(worker)"
                :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
              ></v-switch>
            </v-col>
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-btn
      to="/worker/add"
      fixed
      bottom
      right
      fab
      color="primary"
      v-if="$auth.user().hasPermission(['superadmin'], ['worker_write'])"
    >
      <v-icon>add</v-icon>
    </v-btn>
  </div>
</template>

<script>
import SearchBar from '@/components/general/SearchBar'

export default {
  name: 'workers',
  components: {
    SearchBar
  },
  data() {
    return {
      workersFiltered: [],
      showDeleted: false,
      showActive: true
    }
  },
  methods: {
    filterActive(worker) {
      return (worker.isActive && this.showActive) || (!worker.isActive && !this.showActive)
    },
    update(worker) {
      this.axios.patch('/worker/' + worker.id, { isActive: worker.isActive }).catch(() => {
        this.$swal('Fehler', 'Aktion konnte nicht durchgeführt werden.', 'error')
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
