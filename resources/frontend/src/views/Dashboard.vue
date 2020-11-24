<template>
  <fragment>
    <navigation-bar
      title="Dashoard"
      :loading="$store.getters.isLoading.dashboard"
    ></navigation-bar>
    <v-container>
      <v-row class="mt-4 pt-4">
        <v-col
          cols="12"
          md="8"
          lg="9"
        >
          <v-select
            v-model="dateRange"
            :items="dateRanges"
            @change="loadStats"
          ></v-select>
        </v-col>
        <v-col
          cols="12"
          md="4"
          lg="3"
        >
          <v-checkbox
            v-model="withPreviousYear"
            label="Vorheriges Jahr anzeigen"
            @change="loadStats"
          ></v-checkbox>
        </v-col>
        <v-col
          cols="12"
          lg="6"
        >
          <stats-card
            v-if="stats.employees.hoursByMonth"
            title="Mitarbeiter Stunden"
            text="Geleistete Stunden pro Monat"
            :datasets="stats.employees.hoursByMonth"
          ></stats-card>
        </v-col>
        <v-col
          cols="12"
          lg="6"
        >
          <stats-card
            v-if="stats.workers.hoursByMonth"
            title="Hofmitarbeiter Stunden"
            text="Geleistete Stunden pro Monat"
            :datasets="stats.workers.hoursByMonth"
          ></stats-card>
        </v-col>
        <v-col
          cols="12"
          md="4"
        >
          <single-stat-card
            title="Totale Stunden Mitarbeiter"
            icon="person"
            :value="`${stats.employees.totalHours} Stunden`"
          ></single-stat-card>
        </v-col>
        <v-col
          cols="12"
          md="4"
        >
          <single-stat-card
            title="Aktive Mitarbeiter"
            icon="person"
            :value="`${stats.employees.active}`"
            action-text="Aktuell"
          ></single-stat-card>
        </v-col>
        <v-col
          cols="12"
          md="4"
        >
          <single-stat-card
            title="Totale Stunden Hofmitarbeiter"
            icon="person_outline"
            :value="`${stats.workers.totalHours} Stunden`"
          ></single-stat-card>
        </v-col>
      </v-row>
      <release-notes></release-notes>
    </v-container>
  </fragment>
</template>

<script>
import ReleaseNotes from '@/components/Dashboard/ReleaseNotes'
import StatsCard from '@/components/Dashboard/StatsCard'
import SingleStatCard from '@/components/Dashboard/SingleStatCard'

export default {
  name: 'Dashboard',
  components: {
    ReleaseNotes,
    StatsCard,
    SingleStatCard
  },
  data() {
    return {
      stats: {
        employees: {
          hoursByMonth: null,
          totalHours: null,
          active: null
        },
        workers: {
          hoursByMonth: null,
          totalHours: null
        }
      },
      dateRange: JSON.parse(localStorage.getItem('dashboardDateRange')) || 'last-12-months',
      withPreviousYear: JSON.parse(localStorage.getItem('dashboardWithPreviousYear'))
    }
  },
  computed: {
    workerTotalNumbers() {
      if (!this.stats.workerTotalNumbers) return []
      return [{ text: 'Totale Arbeitsstunden dieses Jahr', value: `${this.stats.workerTotalNumbers.hours}h` }]
    },
    employeeTotalNumbers() {
      if (!this.stats.employeeTotalNumbers) return []
      return [
        { text: 'Totale Arbeitsstunden dieses Jahr', value: `${this.stats.employeeTotalNumbers.hours}h` },
        { text: 'Aktive Mitarbeiter', value: this.stats.employeeTotalNumbers.activeEmployees }
      ]
    },
    dateRanges() {
      const dateRanges = [
        {
          value: 'last-12-months',
          text: 'Letzte 12 Monate'
        },
        {
          value: 'all',
          text: 'Über alles'
        }
      ]

      for (let i = this.$moment().year(); i > 2017; i--) {
        dateRanges.push({
          value: i,
          text: i
        })
      }

      return dateRanges
    }
  },
  watch: {
    dateRange() {
      localStorage.setItem('dashboardDateRange', JSON.stringify(this.dateRange))
    },
    withPreviousYear() {
      localStorage.setItem('dashboardWithPreviousYear', JSON.stringify(this.withPreviousYear))
    }
  },
  mounted() {
    this.loadStats()
  },
  methods: {
    loadStats() {
      this.$store.commit('loading', { dashboard: true })
      this.axios.get('stats', {
        params: {
          dateRange: this.dateRange,
          withPreviousYear: this.withPreviousYear
        }
      }).then(response => {
        this.stats = response.data
      }).catch(() => {
        this.$swal('Fehler', 'Satistiken konnten nicht abgeruffen werden', 'error')
      }).finally(() => {
        this.$store.commit('loading', { dashboard: false })
      })
    }
  }
}
</script>

<style lang="scss">
$ct-grid-color: white;
$ct-text-color: white;
@import 'chartist/dist/scss/chartist.scss';
</style>
