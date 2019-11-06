<template>
  <div class="container">
    <h1 class="text-center">Mitarbeiter-Übersicht</h1>
    <search-bar
      name="employees"
      label="Mitarbeiter suchen"
      :custom-filter-function="filterActive"
      v-model="employeesFiltered"
      @showDeleted="s => showDeleted = s"
      ref="searchBar"
    >
      <v-switch v-model="showActive" label="Aktiv" slot="custom-filter" :disabled="showDeleted"></v-switch>
    </search-bar>
    <v-expansion-panels>
      <v-expansion-panel
        :readonly="$auth.user().hasPermission('', 'employee_preview_read')"
        v-for="employee in employeesFiltered"
        :key="employee.id"
      >
        <v-expansion-panel-header hide-actions>
          <p class="pt-2 mt-1 header-text">
            <v-icon class="float-left">account_circle</v-icon>
            <span class="font-weight-bold pl-2">{{employee.name}}</span>
            <span class="font-italic hidden-xs-only">&nbsp; {{employee.callname}}</span>
          </p>
          <v-btn
            v-if="showDeleted && $auth.user().hasPermission(['superadmin'], ['employee_read'])"
            color="primary"
            max-width="200"
            @click="restoreEmployee(employee)"
          >Wiederherstellen</v-btn>
          <v-btn
            v-else-if="!showDeleted && $auth.user().hasPermission(['superadmin'], ['employee_read'])"
            color="primary"
            max-width="100"
            :to="'/employee/' + employee.id"
          >Details</v-btn>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <v-row>
            <v-col cols="12" md="5" pl-3>
              <h4 class="mb-2">Sprachkenntnisse</h4>
              <p>{{employee.german_knowledge == 1 ? 'Deutsch' : ''}}</p>
              <p>{{employee.english_knowledge == 1 ? 'Englisch' : ''}}</p>
            </v-col>
            <v-col cols="12" md="5">
              <h4 class="mb-2">Zusätzliche Infos</h4>
              <p>{{employee.isDriver == 1 ? 'Fahrer': ''}}</p>
              <p>Kommentar: {{employee.comment}}</p>
            </v-col>
            <v-col cols="12" md="2">
              <v-switch
                class="float-right mr-4 pr-3"
                v-model="employee.isActive"
                label="Aktiv"
                @change="update(employee)"
                :readonly="!$auth.user().hasPermission(['superadmin'], ['employee_write'])"
              ></v-switch>
            </v-col>
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-btn to="/employee/add" fixed bottom right fab color="primary">
      <v-icon>add</v-icon>
    </v-btn>
  </div>
</template>

<script>
import SearchBar from '@/components/general/SearchBar'

export default {
  name: 'employees',
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
  methods: {
    update(employee) {
      this.axios.patch('/employee/' + employee.id, employee).catch(() => {
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
