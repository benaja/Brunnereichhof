<template>
  <div v-if="rapport.customer" class="px-3">
    <h1
      class="text-center my-4"
    >Rapport für "{{rapport.customer.firstname}} {{rapport.customer.lastname}}" der Woche {{getFormatedWeek()}}</h1>
    <v-row>
      <!-- Controls -->
      <v-col cols="12" md="3" class="text-center">
        <v-btn
          color="primary"
          @click="isEmployeePopupOpen = true"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
        >
          <v-icon>edit</v-icon>
          <span class="ml-3">Mitarbeiter bearbeiten</span>
        </v-btn>
      </v-col>
      <v-col cols="12" md="3" class="text-center">
        <v-btn
          color="primary"
          slot="activator"
          @click="selectProjectsModel = true"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
        >
          <v-icon>edit</v-icon>
          <span class="ml-3">Projekte bearbeiten</span>
        </v-btn>
      </v-col>
      <v-col cols="12" md="2" class="text-center">
        <v-btn color="primary" @click="generatePdf">
          <v-icon>picture_as_pdf</v-icon>
          <span class="ml-3">PDF generieren</span>
        </v-btn>
      </v-col>
      <v-col cols="12" md="2" class="text-center">
        <day-total v-model="dayTotalModel"></day-total>
      </v-col>
      <v-col cols="12" md="2">
        <v-checkbox
          label="Abgeschlossen"
          v-model="rapport.isFinished"
          @change="change('isFinished')"
          class="is-finished ma-0"
          justify="center"
          :readonly="!$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
        ></v-checkbox>
      </v-col>

      <!-- Table -->
      <v-col cols="12" class="elevation-1 table">
        <v-row>
          <v-col cols="12" md="2" class="pl-2 d-none d-md-block">
            <p class="font-weight-bold">Wochentag</p>
            <p class="font-weight-bold pt-3 mt-4">Kommentar</p>
            <p class="font-weight-bold all-employees">Alle</p>
            <div
              v-for="rapportdetail of rapport.rapportdetails"
              :key="'e-' + rapportdetail[0].id"
              class="employee-name mx-1 mt-4"
            >
              <p
                class="font-weight-bold"
              >{{employees.find(e => e.id == rapportdetail[0].employee_id).lastname}} {{employees.find(e => e.id == rapportdetail[0].employee_id).firstname}}</p>
            </div>
            <p class="font-weight-bold">Total</p>
          </v-col>
          <v-col cols="12" md="10" class="pt-0">
            <v-row row wrap v-if="rapportLoaded && rapport.rapportdetails.length > 0">
              <v-col
                cols="12"
                md="2"
                class="pt-0"
                v-for="(rapportdetail, key) in rapportdetailsFiltered"
                :key="key"
              >
                <rapport-day
                  :date="date.clone().add(rapportdetail[0].day, 'days')"
                  :rapport="rapport"
                  :day="Number(key)"
                  :projects="projects"
                  :rapportdetails="rapportdetail"
                  :employees="employees"
                ></rapport-day>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12">
        <p
          class="text-center mt-4"
          v-if="$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
        >
          <v-btn color="red" class="white--text mr-4" @click="deleteRapport">Löschen</v-btn>
          <v-btn color="primary" class="save-button ml-4" @click="saveAll">
            Speichern
            <loading-dots v-if="isSaving"></loading-dots>
          </v-btn>
        </p>
      </v-col>
    </v-row>
    <div class="alert">
      <v-alert
        :value="savedSuccessful"
        color="success"
        icon="check_circle"
        transition="slide-y-reverse-transition"
      >Rapport erfolgreich gespeichert.</v-alert>
    </div>
    <edit-employees
      :employees="employees"
      :open="isEmployeePopupOpen"
      :selectedEmployeesProp="selectedEmployees()"
      :defaultProject="rapport.default_project_id"
      @removeEmployee="employeeId => removeEmployee(employeeId)"
      @addEmployee="rapportdetail => rapport.rapportdetails.push(rapportdetail)"
      @close="isEmployeePopupOpen = false"
    ></edit-employees>
    <select-projects
      :projects="projects"
      :rapport="rapport"
      v-model="selectProjectsModel"
      @updatedProjects="updatedProjects => projects = updatedProjects"
    ></select-projects>
  </div>
</template>

<script>
import moment from 'moment'
import EditEmployees from '@/components/Rapport/EditEmployees'
import SelectProjects from '@/components/Rapport/SelectProjects'
import RapportDay from '@/components/Rapport/RapportDay'
import DayTotal from '@/components/Rapport/DayTotal'
import LoadingDots from '@/components/general/LoadingDots'

export default {
  name: 'Rapport',
  components: {
    EditEmployees,
    SelectProjects,
    RapportDay,
    DayTotal,
    LoadingDots
  },
  data() {
    return {
      rapport: {
        rapportdetails: []
      },
      employees: [],
      isEmployeePopupOpen: false,
      rapportdetails: [],
      projects: [],
      selectProjectsModel: false,
      date: moment(),
      rapportLoaded: false,
      dayTotalModel: false,
      rapportdetailsFiltered: [],
      isSaving: false,
      savedSuccessful: false
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios
      .get('rapport/' + this.$route.params.id)
      .then(response => {
        this.rapport = response.data.rapport
        this.date = moment(this.rapport.startdate, 'YYYY-MM-DD', 'de-ch')
        this.employees = response.data.employees
        this.rapport.startdate = new Date(this.rapport.startdate)
        this.getProjects()

        for (let employee of this.employees) {
          employee.name = employee.lastname + ' ' + employee.firstname
          employee.nameWithCallName = employee.name + (employee.callname ? ` (${employee.callname})` : '')
        }

        if (!this.rapport.default_project_id) {
          this.selectProjectsModel = true
        } else if (this.rapport.rapportdetails.length === 0) {
          this.isEmployeePopupOpen = true
        }
        this.rapportLoaded = true
      })
      .catch(() => {
        this.$swal('Fehler', 'Unbekannter Fehler ist aufgetreten', 'error')
      })
  },
  methods: {
    getFormatedWeek() {
      let date = moment(this.rapport.startdate, 'YYYY-MM-DD', 'de-ch')
      return `${date.format('W (DD.MM.YYYY')} - ${date
        .clone()
        .add(6, 'days')
        .format('DD.MM.YYYY')})`
    },
    selectedEmployees() {
      let employees = []
      for (let rapportdetail of this.rapport.rapportdetails) {
        employees.push(this.employees.find(employee => employee.id === rapportdetail[0].employee_id))
      }
      return employees
    },
    removeEmployee(employeeId) {
      let rapportdetail = this.rapport.rapportdetails.find(rapportdetail => rapportdetail[0].employee_id === employeeId)
      this.rapport.rapportdetails.splice(this.rapport.rapportdetails.indexOf(rapportdetail), 1)
      this.rapport.rapportdetails = [...this.rapport.rapportdetails]
    },
    generatePdf() {
      this.axios.get('pdftoken').then(response => {
        window.location = `${process.env.VUE_APP_API_URL}rapport/${this.$route.params.id}/pdf?token=${response.data}`
      })
    },
    change(changedElement) {
      this.$store.commit('isSaving', true)
      this.axios
        .patch(`rapport/${this.rapport.id}`, {
          [changedElement]: this.rapport[changedElement]
        })
        .catch(() => {
          this.$store.commit('isSaving', false)
          this.$swal('Fehler beim speicher', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        })
        .then(() => this.$store.commit('isSaving', false))
    },
    getProjects() {
      this.axios.get(`/customer/${this.rapport.customer_id}/projects`).then(response => {
        this.projects = response.data
        this.$store.commit('isLoading', false)
      })
    },
    deleteRapport() {
      this.$swal({
        title: 'Willst du diesen Rapport wirklich löschen?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'Nein',
        confirmButtonText: 'Ja, Löschen'
      }).then(result => {
        if (result.value) {
          this.axios
            .delete(process.env.VUE_APP_API_URL + 'rapport/' + this.rapport.id)
            .then(() => {
              this.$router.push('/rapport')
            })
            .catch(() => {
              this.$swal('Fehler', 'Rapport konnte nicht gelöscht werden', 'error')
            })
        }
      })
    },
    saveAll() {
      this.isSaving = true
      this.axios
        .put('rapport/' + this.rapport.id, this.rapport)
        .then(response => {
          this.rapport = response.data
          this.isSaving = false
          this.savedSuccessful = true
          setTimeout(() => {
            this.savedSuccessful = false
          }, 3000)
        })
        .catch(() => {
          this.isSaving = false
          this.$swal('Fehler', 'Rapport konnte nicht gespeichet werden.', 'error')
        })
    }
  },
  computed: {
    totalHours() {
      let days = [0, 0, 0, 0, 0, 0]
      for (let rapportdetail of this.rapportdetails) {
        for (const [index, day] of rapportdetail.entries()) {
          days[index] += Number(day.hours)
        }
      }
      return days
    }
  },
  watch: {
    selectProjectsModel() {
      if (!this.selectProjectsModel && this.rapport.rapportdetails.length === 0) {
        this.isEmployeePopupOpen = true
      }
    },
    'rapport.rapportdetails'() {
      let allrapportdetails = this.rapport.rapportdetails.flat()
      this.rapportdetailsFiltered = allrapportdetails.reduce(function(rv, x) {
        ;(rv[x['day']] = rv[x['day']] || []).push(x)
        return rv
      }, {})
    }
  }
}
</script>

<style lang="scss" scoped>
.table {
  background-color: white;
}

.is-finished {
  justify-content: center;
}

.project-popup {
  background-color: white;
  border-radius: 5px;
  padding: 20px;
  width: 600px;
  max-width: 100vw;
}

.all-employees {
  margin-top: 50px;
  margin-bottom: 150px;
}

.employee-name {
  height: 230px;
}

.alert {
  position: fixed;
  bottom: 0;
  right: 5px;
  width: 40%;
}

@media only screen and (max-width: 600px) {
  .alert {
    width: calc(100% - 10px);
  }
}
</style>
