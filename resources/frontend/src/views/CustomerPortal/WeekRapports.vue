<template>
  <v-container>
    <h1 class="text-center display-1">Rapportübersicht</h1>
    <v-row justify="center" class="mt-4">
      <v-col cols="12" lg="8" sm="10">
        <v-list class="pa-0 elevation-1" :two-line="$store.getters.isMobile">
          <template v-for="(rapport, index) of rapports">
            <v-divider v-if="index != 0" :key="index"></v-divider>
            <v-list-item
              :key="-index"
              :to="`/kundenportal/wochenrapport/${rapport.id}`"
              color="primary"
            >
              {{getFormatedWeek(rapport.startdate)}} | {{rapport.hours}} Stunden
              <v-spacer></v-spacer>
              <template v-if="rapport.isFinished">
                <span class="primary--text mr-2">Abgeschlossen</span>
                <v-icon v-if="rapport.isFinished" color="primary">check_circle</v-icon>
              </template>
            </v-list-item>
          </template>
        </v-list>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      rapports: []
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios
      .get('/rapport')
      .then(response => {
        this.rapports = response.data
      })
      .catch(() => {
        this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
      })
      .finally(() => {
        this.$store.commit('isLoading', false)
      })
  },
  methods: {
    getFormatedWeek(date) {
      date = this.$moment(date)
      return `Woche ${date.format('W (DD.MM.YYYY')} - ${date
        .clone()
        .add(6, 'days')
        .format('DD.MM.YYYY')})`
    }
  }
}
</script>
