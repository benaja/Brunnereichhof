<template>
  <v-container>
    <v-row>
      <v-col cols="12" class="px-2">
        <h1>Mitarbeiter</h1>
      </v-col>
      <v-col cols="12" md="6" class="pa-2">
        <hours-by-month :stats="stats.employeeHoursByMonth"></hours-by-month>
      </v-col>
      <v-col cols="12" md="6" class="pa-2">
        <total-numbers :stats="employeeTotalNumbers"></total-numbers>
      </v-col>
      <v-col cols="12" class="px-2 pt-3" v-if="$auth.user().type_id === 3">
        <h1>Hofmitarbeiter</h1>
      </v-col>
      <v-col cols="12" md="6" class="pa-2" v-if="$auth.user().type_id === 3">
        <hours-by-month :stats="stats.workerHoursByMonth"></hours-by-month>
      </v-col>
      <v-col cols="12" md="6" class="pa-2" v-if="$auth.user().type_id === 3">
        <total-numbers :stats="workerTotalNumbers"></total-numbers>
      </v-col>
    </v-row>
    <release-notes></release-notes>
  </v-container>
</template>

<script>
import HoursByMonth from '@/components/Dashboard/HoursByMonth'
import TotalNumbers from '@/components/Dashboard/TotalNumbers'
import ReleaseNotes from '@/components/Dashboard/ReleaseNotes'

export default {
  name: 'Dashboard',
  components: {
    HoursByMonth,
    TotalNumbers,
    ReleaseNotes
  },
  data() {
    return {
      stats: {}
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios.get('stats').then(response => {
      this.stats = response.data
      this.$store.commit('isLoading', false)
    })
  },
  computed: {
    workerTotalNumbers() {
      if (!this.stats.workerTotalNumbers) return []
      return [{ text: 'Totale Arbeitsstunden dieses Jahr', value: this.stats.workerTotalNumbers.hours + 'h' }]
    },
    employeeTotalNumbers() {
      if (!this.stats.employeeTotalNumbers) return []
      return [
        { text: 'Totale Arbeitsstunden dieses Jahr', value: this.stats.employeeTotalNumbers.hours + 'h' },
        { text: 'Aktive Mitarbeiter', value: this.stats.employeeTotalNumbers.activeEmployees }
      ]
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
