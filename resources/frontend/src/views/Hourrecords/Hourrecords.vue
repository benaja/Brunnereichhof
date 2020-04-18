<template>
  <v-container>
    <v-row>
      <v-col cols="12" sm="8" offset-sm="2">
        <h2 class="text-center display-3">{{totalHours}}</h2>
        <h2 class="text-center subheading mb-4">Stunden in disem Jahr</h2>
      </v-col>
    </v-row>
    <hourrecords-chart v-if="!isLoading" :chartData="chartData" :height="250"></hourrecords-chart>
    <v-row class="mt-4">
      <v-col cols="12" md="5" lg="3" class="text-right">
        <v-select
          v-model="sortType"
          :items="sortTypes"
          label="Sortieren nach"
          outline
          prepend-inner-icon="sort"
        ></v-select>
      </v-col>
      <v-col cols="12" md="3" lg="2">
        <date-picker v-model="selectedYear" type="year" label="Jahr"></date-picker>
      </v-col>
      <v-col cols="12" md="4" lg="7">
        <v-btn color="primary" class="float-right" @click="generatePdf">Pdf Erstellen</v-btn>
      </v-col>
    </v-row>
    <v-list class="mt-4 pa-0" v-if="sortType == 'week'">
      <template v-for="(hourrecord, index) of hourrecords">
        <div :key="index" class="week-item">
          <v-list-item :to="'/hourrecords/' + hourrecord[0].year + '/' + hourrecord[0].week">
            <v-list-item-content class="pt-2">
              <p class="mb-0 week-text">
                <span class="font-weight-bold">KW {{hourrecord[0].week}}</span>
                ({{$moment(hourrecord[0].year, 'YYYY').week(hourrecord[0].week).startOf('week').format('DD.MM.YYYY')}} -
                {{$moment(hourrecord[0].year, 'YYYY').week(hourrecord[0].week).endOf('week').format('DD.MM.YYYY')}})
                / {{calculateHours(hourrecord)}} Stunden
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
    <v-expansion-panels v-if="sortType === 'customer'">
      <v-expansion-panel
        v-for="customer of hourrecrodsByCustomer.filter(c => c.hourrecords.length > 0)"
        :key="customer.id"
      >
        <v-expansion-panel-header>
          <p class="mb-1 week-text">
            <span class="font-weight-bold">{{customer.lastname}} {{customer.firstname}}</span>
            / {{ customer.hours }} Stunden
          </p>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <hourrecord-week-list :hourrecords="customer.hourrecords"></hourrecord-week-list>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-expansion-panels v-if="sortType === 'project'">
      <v-expansion-panel
        v-for="project of hourrecrodsByProject.filter(p => p.hourrecords.length > 0)"
        :key="project.id"
      >
        <v-expansion-panel-header>
          <p class="mb-1 week-text">
            <span class="font-weight-bold">{{project.name}}</span>
            / {{ project.hours }} Stunden
          </p>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <hourrecord-week-list :hourrecords="project.hourrecords"></hourrecord-week-list>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <p class="text-center" v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])">
      <v-btn text color="primary" @click="datepicker = true">Angabe hinzufügen</v-btn>
    </p>
    <v-dialog width="unset" v-model="datepicker">
      <v-date-picker v-model="newHourrecordDate" scrollable first-day-of-week="1" locale="ch-de">
        <v-spacer></v-spacer>
        <v-btn text color="primary" @click="datepicker = false">Abbrechen</v-btn>
        <v-btn text color="primary" @click="addHourrecord">OK</v-btn>
      </v-date-picker>
    </v-dialog>
  </v-container>
</template>

<script>
import HourrecordWeekList from '@/components/Hourrecords/HourrecordWeekList'
import HourrecordsChart from '@/components/Hourrecords/HourrecordsChart'
import moment from 'moment'
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
      selectedYear: moment().format('YYYY'),
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
      sortType: 'week'
    }
  },
  mounted() {
    this.randomNumbersInterval = setInterval(() => {
      this.totalHours = Math.floor(Math.random() * 100000)
    }, 10)
    this.getHourRecords()
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
          let labels = []
          let data = []
          for (let i = 1; i <= 52; i++) {
            labels.push(i)
            if (this.hourrecords[i]) {
              let hours = 0
              for (let hourrecord of this.hourrecords[i]) {
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
      let day = (week - 1) * 7
      return new Date(year, 0, day)
    },
    getSundayOfWeek(week, year) {
      let monday = this.getMondayOfWeek(week, year)
      monday.setDate(monday.getDate() + 6)
      return monday
    },
    calculateHours(hourrecords) {
      let hours = 0
      for (let hourrecord of hourrecords) {
        hours += hourrecord.hours
      }
      return hours
    },
    addHourrecord() {
      let newDate
      if (this.newHourrecordDate) {
        newDate = moment(this.newHourrecordDate)
      } else {
        newDate = moment()
      }
      this.$router.push(`/hourrecords/${newDate.format('YYYY')}/${newDate.format('W')}?edit=true`)
    },
    getHourrecordsByCustomer() {
      this.$store.commit('isLoading', true)
      this.axios
        .get(`hourrecord?sortBy=customer&year=${this.selectedYear}`)
        .then(response => {
          for (let customer of response.data) {
            customer.hours = 0
            for (let hourrecord of customer.hourrecords) {
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
          for (let project of response.data) {
            project.hours = 0
            for (let hourrecord of project.hourrecords) {
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
    }
  },
  watch: {
    sortType() {
      this.getHourRecords()
    },
    selectedYear() {
      this.getHourRecords(true)
    }
  }
}
</script>

<style lang="scss" scoped>
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
</style>
