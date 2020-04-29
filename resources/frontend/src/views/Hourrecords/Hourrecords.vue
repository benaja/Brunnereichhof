<template>
  <v-container>
    <h1 class="my-3">
      Stundenangaben
    </h1>
    <div class="grid-layout">
      <div class="chart-card bar-chart">
        <h3 class="title my-2">
          Anzahl Stunden pro KW
        </h3>
        <hourrecords-chart
          v-if="!isLoading"
          :chart-data="chartData"
          :height="250"
          :options="chartOptions"
        ></hourrecords-chart>
      </div>
      <div class="chart-card total-number">
        <h2 class="body-1 mt-4">
          Stunden in disem Jahr
        </h2>
        <h2 class="display-1 mb-4">
          {{ totalHours }}
        </h2>
      </div>
      <div class="chart-card filter">
        <v-select
          v-model="sortType"
          :items="sortTypes"
          label="Sortieren nach"
          outlined
          prepend-inner-icon="sort"
          class="mt-3"
        ></v-select>
        <date-picker
          v-model="selectedYear"
          type="year"
          label="Jahr"
          outlined
        ></date-picker>
        <v-btn
          color="primary"
          class="full-width"
          depressed
          width="100%"
          @click="generatePdf"
        >
          <v-icon class="mr-3">
            picture_as_pdf
          </v-icon>Pdf Erstellen
        </v-btn>
        <v-btn
          v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])"
          outlined
          color="primary"
          width="100%"
          class="my-2"
          @click="datepicker = true"
        >
          <v-icon class="mr-3">
            today
          </v-icon>Erfassen nach KW
        </v-btn>
        <v-btn
          v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])"
          outlined
          color="primary"
          width="100%"
          @click="selectCustomerDialog = true"
        >
          <v-icon class="mr-3">
            supervisor_account
          </v-icon>Erfassen nach Kunde
        </v-btn>
      </div>
    </div>
    <v-list
      v-if="sortType == 'week'"
      class="mt-10 pa-0"
    >
      <template v-for="(hourrecord, index) of hourrecords">
        <div
          :key="index"
          class="week-item"
        >
          <v-list-item :to="'/hourrecords/' + hourrecord[0].year + '/' + hourrecord[0].week">
            <v-list-item-content class="pt-2">
              <p class="mb-0 week-text">
                <span class="font-weight-bold">KW {{ hourrecord[0].week }}</span>
                ({{ $moment().year(hourrecord[0].year).week(hourrecord[0].week)
                  .startOf('week').format('DD.MM.YYYY') }} -
                {{ $moment().year(hourrecord[0].year).week(hourrecord[0].week)
                  .endOf('week').format('DD.MM.YYYY') }})
                / {{ calculateHours(hourrecord) }} Stunden
              </p>
            </v-list-item-content>
            <v-list-item-action class="hidden-xs-only">
              <v-icon>send</v-icon>
            </v-list-item-action>
          </v-list-item>
          <v-divider class="divider"></v-divider>
        </div>
      </template>
    </v-list>
    <v-expansion-panels
      v-if="sortType === 'customer'"
      class="mt-10"
      flat
    >
      <v-expansion-panel
        v-for="customer of hourrecrodsByCustomer.filter(c => c.hourrecords.length > 0)"
        :key="customer.id"
        class="elevation-0"
      >
        <v-expansion-panel-header hide-actions>
          <p class="mb-1 week-text">
            <span class="font-weight-bold">{{ customer.lastname }} {{ customer.firstname }}</span>
            / {{ customer.hours }} Stunden
          </p>
          <v-btn
            max-width="100"
            color="primary"
            text
            :to="`/customers/${customer.id}/hourrecords?year=${selectedYear}`"
          >
            Details
          </v-btn>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <hourrecord-week-list :hourrecords="customer.hourrecords"></hourrecord-week-list>
        </v-expansion-panel-content>
        <v-divider></v-divider>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-expansion-panels
      v-if="sortType === 'project'"
      class="mt-10"
      flat
    >
      <v-expansion-panel
        v-for="project of hourrecrodsByProject.filter(p => p.hourrecords.length > 0)"
        :key="project.id"
      >
        <v-expansion-panel-header>
          <p class="mb-1 week-text">
            <span class="font-weight-bold">{{ project.name }}</span>
            / {{ project.hours }} Stunden
          </p>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <hourrecord-week-list :hourrecords="project.hourrecords"></hourrecord-week-list>
        </v-expansion-panel-content>
        <v-divider></v-divider>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-dialog
      v-model="datepicker"
      width="unset"
    >
      <v-date-picker
        v-model="newHourrecordDate"
        scrollable
        first-day-of-week="1"
        locale="ch-de"
        show-week
      >
        <v-spacer></v-spacer>
        <v-btn
          text
          color="primary"
          @click="datepicker = false"
        >
          Abbrechen
        </v-btn>
        <v-btn
          text
          color="primary"
          @click="addHourrecord"
        >
          OK
        </v-btn>
      </v-date-picker>
    </v-dialog>
    <v-dialog
      v-model="selectCustomerDialog"
      width="500"
    >
      <v-card>
        <v-card-title>Kunde Auswählen</v-card-title>
        <v-card-text>
          <v-select
            label="Kunde"
            :items="customers"
            item-value="id"
            item-text="name"
            @input="selectCustomer"
          ></v-select>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import HourrecordWeekList from '@/components/Hourrecords/HourrecordWeekList'
import HourrecordsChart from '@/components/Hourrecords/HourrecordsChart'
import DatePicker from '@/components/general/DatePicker'
import { downloadFile } from '@/utils'

export default {
  name: 'Hourrecords',
  components: {
    HourrecordsChart,
    HourrecordWeekList,
    DatePicker
  },
  data() {
    return {
      chartData: {
        labels: [],
        datasets: [
          {
            label: 'Anzahl Stunden',
            backgroundColor: '#26a69a',
            data: []
          }
        ]
      },
      chartOptions: {
        legend: {
          display: false
        },
        scales: {
          xAxes: [
            {
              gridLines: {
                display: false
              }
            }
          ]
        }
      },
      selectedYear: this.$moment().format('YYYY'),
      hourrecords: {},
      hourrecrodsByCustomer: [],
      hourrecrodsByProject: [],
      isLoading: true,
      datepicker: false,
      newHourrecordDate: null,
      totalHours: 0,
      randomNumbersInterval: null,
      sortTypes: [
        { text: 'Woche', value: 'week' },
        { text: 'Kunde', value: 'customer' },
        { text: 'Projekt', value: 'project' }
      ],
      sortType: 'week',
      selectCustomerDialog: false,
      customers: []
    }
  },
  watch: {
    sortType() {
      this.getHourRecords()
    },
    selectedYear() {
      this.getHourRecords(true)
    }
  },
  mounted() {
    this.randomNumbersInterval = setInterval(() => {
      this.totalHours = Math.floor(Math.random() * 100000)
    }, 10)
    this.getHourRecords()
    this.$store.dispatch('customers').then(customers => {
      this.customers = customers
    })
  },
  methods: {
    getHourRecords(updateStats = false) {
      if (this.sortType === 'week' || updateStats) {
        this.getHourRecordsByWeek()
      }
      if (this.sortType === 'customer') {
        this.getHourrecordsByCustomer()
      } else if (this.sortType === 'project') {
        this.getHourrecordsByProject()
      }
    },
    getHourRecordsByWeek() {
      this.$store.commit('isLoading', true)
      this.axios
        .get(`hourrecord?year=${this.selectedYear}`)
        .then(response => {
          this.hourrecords = response.data
          clearInterval(this.randomNumbersInterval)
          this.totalHours = 0
          const labels = []
          const data = []
          for (let i = 1; i <= 52; i++) {
            labels.push(i)
            if (this.hourrecords[i]) {
              let hours = 0
              for (const hourrecord of this.hourrecords[i]) {
                hours += hourrecord.hours
                this.totalHours += hourrecord.hours
              }
              data.push(hours)
            } else {
              data.push(0)
            }
          }
          this.chartData = {
            labels,
            datasets: [
              {
                label: 'Anzahl Stunden',
                backgroundColor: '#26a69a',
                data
              }
            ]
          }
          this.$store.commit('isLoading', false)
          this.isLoading = false
        })
        .catch(() => {
          this.$swal('Fehler', 'Daten konnten nicht abgeruffen werden', 'error')
          this.isLoading = false
          this.$store.commit('isLoading', false)
          clearInterval(this.randomNumbersInterval)
          this.totalHours = 0
        })
    },
    getMondayOfWeek(week, year) {
      const day = (week - 1) * 7
      return new Date(year, 0, day)
    },
    getSundayOfWeek(week, year) {
      const monday = this.getMondayOfWeek(week, year)
      monday.setDate(monday.getDate() + 6)
      return monday
    },
    calculateHours(hourrecords) {
      let hours = 0
      for (const hourrecord of hourrecords) {
        hours += hourrecord.hours
      }
      return hours
    },
    addHourrecord() {
      let newDate
      if (this.newHourrecordDate) {
        newDate = this.$moment(this.newHourrecordDate)
      } else {
        newDate = this.$moment()
      }
      this.$router.push(`/hourrecords/${newDate.format('YYYY')}/${newDate.format('W')}?edit=true`)
    },
    getHourrecordsByCustomer() {
      this.$store.commit('isLoading', true)
      this.axios
        .get(`hourrecord?sortBy=customer&year=${this.selectedYear}`)
        .then(response => {
          for (const customer of response.data) {
            customer.hours = 0
            for (const hourrecord of customer.hourrecords) {
              customer.hours += hourrecord.hours
            }
          }
          this.$store.commit('isLoading', false)
          this.hourrecrodsByCustomer = response.data
        })
        .catch(() => {
          this.$store.commit('isLoading', false)
          this.$swal('Fehler', 'Beim Abfragen der Daten ist ein unerwarteter Fehler aufgetreten.', 'error')
        })
    },
    getHourrecordsByProject() {
      this.$store.commit('isLoading', true)
      this.axios
        .get(`hourrecord?sortBy=project&year=${this.selectedYear}`)
        .then(response => {
          for (const project of response.data) {
            project.hours = 0
            for (const hourrecord of project.hourrecords) {
              project.hours += hourrecord.hours
            }
          }
          this.hourrecrodsByProject = response.data
          this.$store.commit('isLoading', false)
        })
        .catch(() => {
          this.$store.commit('isLoading', false)
          this.$swal('Fehler', 'Beim Abfragen der Daten ist ein unerwarteter Fehler aufgetreten.', 'error')
        })
    },
    generatePdf() {
      downloadFile(`pdf/hourrecord/${this.selectedYear}/customer/all`)
    },
    selectCustomer(customerId) {
      this.$router.push({
        name: 'CustomerHourrecords',
        params: { id: customerId },
        query: {
          year: this.selectedYear,
          edit: true,
          hourrecordDialog: true
        }
      })
    }
  },
  url: {
    selectedYear: {
      param: 'year',
      noHistory: true
    },
    sortType: {
      param: 'sortType',
      noHistory: true
    }
  }
}
</script>

<style lang="scss" scoped>
.grid-layout {
  display: grid;
  grid-template-rows: auto auto;
  grid-template-columns: 70% 30%;
  grid-gap: 20px;
  grid-template-areas:
    'bar-chart total-number'
    'bar-chart filter';
}

.bar-chart {
  grid-area: bar-chart;
}

.total-number {
  grid-area: total-number;
}

.filter {
  grid-area: filter;
}

.chart-card {
  background-color: white;
  box-shadow: 0 0 30px lightgray;
  border-radius: 20px;
  padding: 10px 20px;
}

.week-item:last-child {
  .divider {
    display: none;
  }
}
.week-text {
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  width: 100%;
}

@media only screen and (max-width: 1264px) {
  .grid-layout {
    grid-template-columns: 65% 35%;
  }
}

@media only screen and (max-width: 1264px) {
  .grid-layout {
    grid-template-columns: 100%;
    grid-template-rows: auto auto auto;
    grid-template-areas: 'total-number' 'bar-chart' 'filter';
  }
}
</style>
