<template>
  <fragment>
    <navigation-bar title="Mitarbeiter"></navigation-bar>
    <v-container>
      <search-bar
        ref="searchBar"
        v-model="employeesFiltered"
        name="employees"
        label="Mitarbeiter suchen"
        :custom-filter-function="filterActive"
        :items="allEmployees"
        @showDeleted="s => showDeleted = s"
      >
        <v-switch
          slot="custom-filter"
          v-model="showActive"
          label="Aktiv"
          :disabled="showDeleted"
        ></v-switch>
      </search-bar>
      <progress-linear :loading="$store.getters.isLoading.employees"></progress-linear>
      <v-expansion-panels>
        <v-expansion-panel
          v-for="employee in employeesFiltered"
          :key="employee.id"
          :readonly="!$auth.user().hasPermission('superadmin', 'employee_read')"
        >
          <v-expansion-panel-header hide-actions>
            <p class="pt-2 mt-1 header-text">
              <v-icon class="float-left">
                account_circle
              </v-icon>
              <span class="font-weight-bold pl-2">{{ employee.name }}</span>
              <span class="font-italic hidden-xs-only">&nbsp; {{ employee.callname }}</span>
            </p>
            <v-btn
              v-if="showDeleted && $auth.user().hasPermission(['superadmin'], ['employee_write'])"
              color="primary"
              max-width="200"
              depressed
              @click="restoreEmployee(employee)"
            >
              Wiederherstellen
            </v-btn>
            <v-btn
              v-else-if="!showDeleted &&
                $auth.user().hasPermission(['superadmin'], ['employee_read'])"
              color="primary"
              max-width="100"
              depressed
              :to="'/employee/' + employee.id"
            >
              Details
            </v-btn>
          </v-expansion-panel-header>
          <v-expansion-panel-content>
            <v-row>
              <v-col
                cols="12"
                md="5"
                pl-3
              >
                <h4 class="mb-2">
                  Sprachkenntnisse
                </h4>
                <p
                  v-for="language of employee.languages"
                  :key="language.id"
                  class="mb-1"
                >
                  {{ language.name }}
                </p>
              </v-col>
              <v-col
                cols="12"
                md="5"
              >
                <h4 class="mb-2">
                  Zusätzliche Infos
                </h4>
                <p>{{ employee.isDriver == 1 ? 'Fahrer': '' }}</p>
                <p>Kommentar: {{ employee.comment }}</p>
              </v-col>
              <v-col
                cols="12"
                md="2"
              >
                <v-switch
                  v-model="employee.isActive"
                  class="float-right mr-4 pr-3"
                  label="Aktiv"
                  :readonly="!$auth.user().hasPermission(['superadmin'], ['employee_write'])"
                  @change="update(employee)"
                ></v-switch>
              </v-col>
            </v-row>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['employee_write'])"
        to="/employee/add"
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
  components: {
    SearchBar
  },
  data() {
    return {
      showDeleted: false,
      showActive: true,
      employeesFiltered: []
    }
  },
  computed: {
    ...mapGetters(['allEmployees'])
  },
  mounted() {
    this.$store.dispatch('fetchEmployees')
  },
  methods: {
    update(employee) {
      this.axios.patch(`/employee/${employee.id}`, employee).catch(() => {
        employee.isActive = !employee.isActive
        this.$swal('Fehler', 'Aktion konnte nicht durchgeführt werden.', 'error')
      })
    },
    filterActive(employee) {
      return (employee.isActive && this.showActive) || (!employee.isActive && !this.showActive)
    },
    restoreEmployee(employee) {
      this.$refs.searchBar.restoreItem(employee)
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

.header-text {
  line-height: 1.8em;
  vertical-align: middle;
}
</style>

<style lang="scss">
.filter-controlls {
  .v-input__control {
    margin: 0 auto;
  }
}
</style>
