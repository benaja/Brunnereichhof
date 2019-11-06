<template>
  <v-row justify="center">
    <v-dialog v-model="isOpen" max-width="600px" :persistent="selectedEmployees.length === 0">
      <v-card width="600px">
        <v-card-title>
          <h3>Mitarbeiter hinzufügen</h3>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-autocomplete
            label="Mitarbeiter suchen"
            append-outer-icon="search"
            v-model="selectedEmployee"
            :items="employees.filter(e => e.isActive && !e.deleted_at && !selectedEmployees.includes(e))"
            item-value="id"
            item-text="nameWithCallName"
            @input="addEmployee"
            no-data-text="Keine Mitarbeiter verfügbar"
          ></v-autocomplete>
          <v-list>
            <v-list-item v-for="(employee, index) of selectedEmployees" :key="index">
              <v-list-item-content>{{employee.name}}</v-list-item-content>
              <v-list-item-avatar class="pointer">
                <v-icon color="primary" @click="removeEmployee(employee)">delete</v-icon>
              </v-list-item-avatar>
            </v-list-item>
          </v-list>
          <v-row justify="center">
            <v-btn
              color="primary"
              @click="isOpen = false"
              :disabled="selectedEmployees.length === 0"
            >Speichern</v-btn>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  name: 'EditEmployees',
  components: {},
  props: ['employees', 'selectedEmployeesProp', 'open', 'defaultProject'],
  data() {
    return {
      isOpen: false,
      selectedEmployee: null,
      selectedEmployees: this.selectedEmployeesProp
    }
  },
  mounted() {
    this.isOpen = this.open
  },
  methods: {
    addEmployee() {
      if (this.selectedEmployees.filter(employee => employee.id === this.selectedEmployee).length === 0) {
        this.selectedEmployees.push(this.employees.filter(employee => employee.id === this.selectedEmployee)[0])
        this.axios
          .post(process.env.VUE_APP_API_URL + 'rapport/' + this.$route.params.id + '/employee', {
            employee_id: this.selectedEmployee,
            default_project_id: this.defaultProject
          })
          .then(response => {
            this.$emit('addEmployee', response.data)
          })
          .catch(error => {
            if (error.response.data.includes('employee already exists')) {
              this.$swal('Mitarbeiter ist bereits hinzugefügt worden', '', 'warning')
            } else {
              let employee = this.selectedEmployees.filter(employee => employee.id === this.selectedEmployee)
              this.selectedEmployees.splice(this.selectedEmployees.indexOf(employee), 1)
              this.selectedEmployees = [...this.selectedEmployees]
              this.$swal('Fehler', 'Es ist ein unbekannter Fehler beim Speichern aufgetreten', 'error')
            }
          })
      }
    },
    removeEmployee(employee) {
      this.$swal({
        title: 'Achtung!',
        text: `Willst du den Mitarbeiter ${employee.name} wirklich von diesem Rapport entfernen?`,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ja, entfernen!',
        cancelButtonText: 'Nein, abbrechen'
      }).then(result => {
        if (result.value) {
          this.selectedEmployees.splice(this.selectedEmployees.indexOf(employee), 1)
          this.selectedEmployees = [...this.selectedEmployees]
          this.axios
            .delete(process.env.VUE_APP_API_URL + 'rapport/' + this.$route.params.id + '/employee/' + employee.id)
            .then(response => {
              this.$emit('removeEmployee', employee.id)
            })
            .catch(() => {
              this.selectedEmployees.push(this.employees.filter(currentEmployee => currentEmployee.id === employee.id)[0])
              this.$swal('Fehler', 'Mitarbeiter konnte nicht entfernt werden', 'error')
            })
        }
      })
    }
  },
  watch: {
    open() {
      this.isOpen = this.open
    },
    isOpen() {
      if (!this.isOpen) {
        this.$emit('close')
      }
    },
    selectedEmployeesProp() {
      this.selectedEmployees = this.selectedEmployeesProp
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
