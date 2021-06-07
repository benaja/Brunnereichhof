<template>
  <fragment>
    <navigation-bar
      title="Rapport bearbeiten"
      :loading="isLoading"
      full-width
    ></navigation-bar>
    <div
      v-if="rapport.customer"
      class="px-3"
    >
      <h1 class="my-4 display-1">
        "{{ rapport.customer.firstname }} {{ rapport.customer.lastname }}"
        / Woche {{ formatedWeek }}
      </h1>
      <div class="d-flex justify-space-between flex-wrap">
        <v-btn
          color="primary"
          class="my-2"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
          outlined
          @click="isEmployeePopupOpen = true"
        >
          <v-icon>edit</v-icon>
          <span class="ml-3">Mitarbeiter bearbeiten</span>
        </v-btn>
        <v-btn
          slot="activator"
          color="primary"
          class="my-2"
          outlined
          :disabled="!$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
          @click="selectProjectsModel = true"
        >
          <v-icon>edit</v-icon>
          <span class="ml-3">Projekte bearbeiten</span>
        </v-btn>

        <v-btn
          color="primary"
          outlined
          :loading="loadingPdf"
          class="my-2"
          @click="generatePdf"
        >
          <v-icon>picture_as_pdf</v-icon>
          <span class="ml-3">PDF generieren</span>
        </v-btn>
        <day-total v-model="dayTotalModel"></day-total>
        <v-checkbox
          v-model="rapport.isFinished"
          label="Abgeschlossen"
          class="my-2 "
          :readonly="!$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
          @change="debounce('isFinished', $event)"
        ></v-checkbox>
      </div>
      <div>
        <!-- Table -->
        <table class="rapport-table">
          <tr>
            <th>Wochentag</th>
            <th class="weekday">
              Montag
            </th>
            <th class="weekday">
              Dienstag
            </th>
            <th class="weekday">
              Mittwoch
            </th>
            <th class="weekday">
              Donnerstag
            </th>
            <th class="weekday">
              Freitag
            </th>
            <th class="weekday">
              Samstag
            </th>
          </tr>

          <RapportComments :rapport="rapport"></RapportComments>

          <tr>
            <th class="pt-2">
              Alle
            </th>
            <EditAllEmployees
              v-for="(rapportdetailsByDay, index) of rapportdetailsByDays"
              :key="`i-${index}`"
              :rapportdetails="rapportdetailsByDay"
              :rapport="rapport"
              :settings="settings"
              :projects="projects"
            ></EditAllEmployees>
          </tr>

          <RapportEmployee
            v-for="(rapportdetailsByEmployee, index) of rapport.rapportdetails"
            :key="index"
            :rapportdetails="rapportdetailsByEmployee"
            :rapport="rapport"
            :settings="settings"
            :projects="projects"
          ></RapportEmployee>

          <tr class="total-hours">
            <th>Total</th>
            <td
              v-for="(rapportdetailsByDay, index) of rapportdetailsByDays"
              :key="`t-${index}`"
            >
              {{ totalHoursOfRapportdetails(rapportdetailsByDay) }}
            </td>
          </tr>
        </table>
        <v-col cols="12">
          <p
            v-if="$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
            class="text-center mt-4"
          >
            <v-btn
              color="red"
              class="white--text mr-4"
              depressed
              @click="deleteRapport"
            >
              Löschen
            </v-btn>
            <v-btn
              color="primary"
              class="save-button ml-4"
              depressed
              @click="saveAll"
            >
              Speichern
              <loading-dots v-if="isSaving"></loading-dots>
            </v-btn>
          </p>
        </v-col>
      </div>
      <div class="alert">
        <v-alert
          :value="savedSuccessful"
          icon="check_circle"
          transition="slide-y-reverse-transition"
          type="success"
        >
          Rapport erfolgreich gespeichert.
        </v-alert>
      </div>
      <edit-employees
        :rapport="rapport"
        :open="isEmployeePopupOpen"
        @close="isEmployeePopupOpen = false"
      ></edit-employees>
      <select-projects
        v-model="selectProjectsModel"
        :projects="projects"
        :rapport="rapport"
        @updatedProjects="updatedProjects => projects = updatedProjects"
      ></select-projects>
    </div>
  </fragment>
</template>

<script>
import moment from 'moment'
import EditEmployees from '@/components/Rapport/EditEmployees'
import SelectProjects from '@/components/Rapport/SelectProjects'
import RapportDay from '@/components/Rapport/RapportDay'
import RapportComments from '@/components/Rapport/RapportComments'
import RapportEmployee from '@/components/Rapport/RapportEmployee'
import EditAllEmployees from '@/components/Rapport/EditAllEmployees'
import DayTotal from '@/components/Rapport/DayTotal'
import LoadingDots from '@/components/general/LoadingDots'
import { downloadFile } from '@/utils'
import { mapGetters } from 'vuex'
import _ from 'lodash'

export default {
  components: {
    EditEmployees,
    SelectProjects,
    RapportDay,
    DayTotal,
    LoadingDots,
    RapportComments,
    RapportEmployee,
    EditAllEmployees
  },
  data() {
    return {
      rapport: {
        rapportdetails: []
      },
      isEmployeePopupOpen: false,
      rapportdetails: [],
      projects: [],
      selectProjectsModel: false,
      date: moment(),
      rapportLoaded: false,
      dayTotalModel: false,
      rapportdetailsFiltered: [],
      isSaving: false,
      savedSuccessful: false,
      isLoading: false,
      loadingPdf: false
    }
  },
  computed: {
    ...mapGetters({
      settings: 'settings',
      employees: 'allEmployees'
    }),
    totalHours() {
      const days = [0, 0, 0, 0, 0, 0]
      for (const rapportdetail of this.rapportdetails) {
        for (const [index, day] of rapportdetail.entries()) {
          days[index] += Number(day.hours)
        }
      }
      return days
    },
    formatedWeek() {
      const date = moment(this.rapport.startdate, 'YYYY-MM-DD', 'de-ch')
      return `${date.format('W (DD.MM.YYYY')} - ${date
        .clone()
        .add(6, 'days')
        .format('DD.MM.YYYY')})`
    },
    pageTitle() {
      if (this.rapport.customer) {
        return `Rapport für "${this.rapport.customer.firstname}
        ${this.rapport.customer.lastname}" der Woche ${this.formatedWeek}`
      }
      return null
    },
    rapportdetailsByDays() {
      const startdate = this.$moment(this.rapport.startdate, 'YYYY-MM-DD')
      const rapportdetails = this.rapport.rapportdetails.flat()

      const days = []
      for (let i = 0; i < 6; i++) {
        const day = startdate.clone().add(i, 'days').format('YYYY-MM-DD')
        days.push(rapportdetails.filter(r => r.date === day))
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
    'rapport.rapportdetails': function () {
      const allrapportdetails = this.rapport.rapportdetails.flat()
      this.rapportdetailsFiltered = allrapportdetails.reduce((rv, x) => {
        (rv[x.day] = rv[x.day] || []).push(x)
        return rv
      }, {})
    }
  },
  mounted() {
    this.isLoading = true
    this.axios
      .get(`rapports/${this.$route.params.id}`)
      .then(response => {
        this.rapport = response.data.rapport
        this.date = moment(this.rapport.startdate, 'YYYY-MM-DD', 'de-ch')
        this.rapport.startdate = new Date(this.rapport.startdate)
        this.getProjects()
        this.$store.dispatch('fetchEmployees')

        if (!this.rapport.default_project_id) {
          this.selectProjectsModel = true
        } else if (this.rapport.rapportdetails.length === 0) {
          this.isEmployeePopupOpen = true
        }

        this.rapportLoaded = true
      })
      .catch(() => {
        this.$swal('Fehler', 'Unbekannter Fehler ist aufgetreten', 'error')
      }).finally(() => {
        this.isLoading = false
      })

    this.$store.dispatch('fetchSettings')
  },
  methods: {
    debounce: _.debounce(function(key) {
      this.change(key)
    }, 400),
    generatePdf() {
      this.loadingPdf = true
      downloadFile(`pdf/rapports/${this.$route.params.id}`).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Erstellen des PDFs')
      }).finally(() => {
        this.loadingPdf = false
      })
    },
    change(changedElement) {
      this.$store.commit('isSaving', true)
      this.axios
        .patch(`rapports/${this.rapport.id}`, {
          [changedElement]: this.rapport[changedElement]
        })
        .catch(() => {
          this.$swal('Fehler beim speicher', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        })
        .finally(() => this.$store.commit('isSaving', false))
    },
    getProjects() {
      this.axios.get(`/customers/${this.rapport.customer_id}/projects`).then(response => {
        this.projects = response.data
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
            .delete(`rapports/${this.rapport.id}`)
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
        .put(`rapports/${this.rapport.id}`, this.rapport)
        .then(() => {
          this.savedSuccessful = true
          setTimeout(() => {
            this.savedSuccessful = false
          }, 3000)
        })
        .catch(() => {
          this.$swal('Fehler', 'Rapport konnte nicht gespeichet werden.', 'error')
        }).finally(() => {
          this.isSaving = false
        })
    },
    totalHoursOfRapportdetails(rapportdetails) {
      return rapportdetails
        .reduce((total, rapportdetail) => Number(total) + Number(rapportdetail.hours || 0), 0)
    }
  }
}
</script>

<style lang="scss" scoped>
.table {
  background-color: white;
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
  margin-bottom: 170px;

  &.small-height {
    margin-bottom: 110px;
  }
}

.employee-name {
  height: 220px;

  &.small-height {
    height: 170px;
  }
}

.alert {
  position: fixed;
  bottom: 0;
  right: 5px;
  width: 40%;
}

.rapport-table {
  width: 100%;

  th, td
  {
    text-align: left;
  }

  th {
    vertical-align: top;
  }


  .weekday {
    padding: 5px 10px;
  }
}

.total-hours {
  td {
    padding: 0 10px;
  }
}

@media only screen and (max-width: 600px) {
  .alert {
    width: calc(100% - 10px);
  }
}
</style>
