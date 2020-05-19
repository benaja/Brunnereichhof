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
          md="6"
        >
          <stats-card
            title="Mitarbeiter Stunden"
            text="Geleistete Stunden pro Monat"
            :updated-at="stats.updatedAt.date"
            :dataset="stats.employeeHoursByMonth"
          ></stats-card>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <stats-card
            title="Hofmitarbeiter Stunden"
            text="Geleistete Stunden pro Monat"
            :updated-at="stats.updatedAt.date"
            :dataset="stats.workerHoursByMonth"
          ></stats-card>
        </v-col>
        <v-col
          cols="12"
          md="4"
        >
          <single-stat-card
            title="Totale Stunden Mitarbeiter"
            icon="person"
            :value="`${stats.employeeTotalNumbers.hours} Stunden`"
            :updated-at="stats.updatedAt.date"
          ></single-stat-card>
        </v-col>
        <v-col
          cols="12"
          md="4"
        >
          <single-stat-card
            title="Aktive Mitarbeiter"
            icon="person"
            :value="`${stats.employeeTotalNumbers.activeEmployees}`"
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
            :value="`${stats.workerTotalNumbers.hours} Stunden`"
            :updated-at="stats.updatedAt.date"
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
        employeeTotalNumbers: {},
        employeeHoursByMonth: [],
        workerTotalNumbers: {},
        updatedAt: {}
      }
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
    }
  },
  mounted() {
    this.$store.commit('loading', { dashboard: true })
    this.axios.get('stats').then(response => {
      this.stats = response.data
    }).catch(() => {
      this.$swal('Fehler', 'Satistiken konnten nicht abgeruffen werden', 'error')
    }).finally(() => {
      this.$store.commit('loading', { dashboard: false })
    })
  }
}
</script>

<style lang="scss">
$ct-grid-color: white;
$ct-text-color: white;
@import 'chartist/dist/scss/chartist.scss';
</style>
