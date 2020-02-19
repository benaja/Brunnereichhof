<template>
  <div>
    <h1 class="text-center">Rapportübersicht</h1>
    <v-row justify="center" class="mt-4">
      <v-col cols="12" lg="6" sm10>
        <v-list class="pa-0 elevation-1" :two-line="$store.getters.isMobile">
          <template v-for="(rapport, index) of rapports">
            <v-divider v-if="index != 0" :key="index"></v-divider>
            <v-list-item
              :key="-index"
              :to="'/rapport/week/' + rapport.date.format('DD.MM.YYYY')"
              color="primary"
            >
              <v-list-item-content>{{getFormatedWeek(rapport.date)}} | {{rapport.hours}} Stunden</v-list-item-content>
              <v-list-item-avatar>
                <v-icon v-if="rapport.isFinished" color="primary">check_circle</v-icon>
              </v-list-item-avatar>
            </v-list-item>
          </template>
        </v-list>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  data() {
    return {
      rapports: []
    }
  },
  mounted() {
    this.axios
      .get('/rapport')
      .then(response => {
        this.rapports = response.data
      })
      .catch(() => {
        this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
      })
  }
}
</script>

<style>
</style>
