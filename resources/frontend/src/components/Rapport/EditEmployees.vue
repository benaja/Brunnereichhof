<template>
  <v-row justify="center">
    <v-dialog
      :value="open"
      max-width="600px"
      :persistent="selectedEmployees.length === 0"
      @input="$emit('close')"
    >
      <v-card width="600px">
        <v-card-title>
          <h3>Mitarbeiter hinzufügen</h3>
        </v-card-title>
        <v-divider></v-divider>
        <progress-linear :loading="isLoading"></progress-linear>
        <v-card-text class="card-content">
          <v-autocomplete
            v-model="selectedEmployee"
            label="Mitarbeiter suchen"
            append-outer-icon="search"
            :items="employees.filter(e => e.isActive && !selectedEmployees.includes(e))"
            item-value="id"
            item-text="nameWithCallName"
            no-data-text="Keine Mitarbeiter verfügbar"
            @input="addEmployee"
          ></v-autocomplete>
          <v-list dense>
            <v-list-item
              v-for="(employee, index) of selectedEmployees"
              :key="index"
            >
              <v-list-item-content>
                {{ employee.name }}
                <i>{{ employee.callname }}</i>
              </v-list-item-content>
              <v-list-item-avatar class="pointer">
                <v-icon
                  color="primary"
                  @click="removeEmployee(employee)"
                >
                  delete
                </v-icon>
              </v-list-item-avatar>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="py-4">
          <v-btn
            color="primary"
            class="mx-auto px-4"
            :disabled="selectedEmployees.length === 0"
            depressed
            @click="$emit('close')"
          >
            Fertig
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import { mapGetters } from 'vuex'
import { confirmAction } from '@/utils'

export default {
  props: {
    rapport: {
      type: Object,
      default: null
    },
    open: {
      type: Boolean
    },
    defaultProject: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      selectedEmployee: null,
      isLoading: false
    }
  },
  computed: {
    ...mapGetters(['employees', 'allEmployees']),
    selectedEmployees() {
      if (this.allEmployees.length) {
        const employees = this.rapport.rapportdetails
          .map(rapportdetail => this.allEmployees.find(e => e.id === rapportdetail[0].employee_id))
          .sort((a, b) => a.name.toLowerCase().localeCompare(b.name.toLowerCase()))
        return employees
      }
      return []
    }
  },
  methods: {
    addEmployee() {
      const employeeSelected = this.selectedEmployees
        .find(employee => employee.id === this.selectedEmployee)
      if (!employeeSelected) {
        this.isLoading = true
        this.axios
          .post(`/rapports/${this.$route.params.id}/employees`, {
            employee_id: this.selectedEmployee,
            default_project_id: this.rapport.default_project_id
          })
          .then(response => {
            this.rapport.rapportdetails.push(response.data)
            this.rapport.rapportdetails = [...this.rapport.rapportdetails]
            this.selectedEmployee = null
          })
          .catch(error => {
            if (error.includes('employee already exists')) {
              this.$swal('Mitarbeiter ist bereits hinzugefügt worden', '', 'warning')
            } else {
              this.$swal('Fehler', 'Es ist ein unbekannter Fehler beim Speichern aufgetreten', 'error')
            }
          }).finally(() => {
            this.isLoading = false
          })
      }
    },
    removeEmployee(employee) {
      confirmAction(
        `Willst du den Mitarbeiter ${employee.name} wirklich von diesem Rapport entfernen?`,
        'Ja, entfernen!'
      ).then(result => {
        if (result) {
          this.isLoading = true
          this.axios
            .delete(`rapports/${this.$route.params.id}/employee/${employee.id}`)
            .then(() => {
              const rapportdetail = this.rapport.rapportdetails
                .find(r => r[0].employee_id === employee.id)
              const index = this.rapport.rapportdetails.indexOf(rapportdetail)
              this.rapport.rapportdetails.splice(index, 1)
              this.rapport.rapportdetails = [...this.rapport.rapportdetails]
            })
            .catch(() => {
              this.$swal('Fehler', 'Mitarbeiter konnte nicht entfernt werden', 'error')
            }).finally(() => {
              this.isLoading = false
            })
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.card-content {
  max-height: 70vh;
  overflow-y: auto;
}
</style>
