<template>
  <fragment>
    <navigation-bar
      title="Rapportübersicht"
      :loading="isLoading"
    ></navigation-bar>
    <v-container>
      <v-row
        justify="center"
        class="mt-4"
      >
        <v-col
          cols="12"
          lg="6"
          sm10
        >
          <v-list
            class="pa-0 elevation-1"
            :two-line="$store.getters.isMobile"
          >
            <template v-for="(rapport, index) of rapports">
              <v-divider
                v-if="index != 0"
                :key="index"
              ></v-divider>
              <v-list-item
                :key="-index"
                :to="'/rapport/week/' + rapport.date.format('DD.MM.YYYY')"
                color="primary"
              >
                <v-list-item-content>
                  {{ getFormatedWeek(rapport.date) }} | {{ rapport.hours }} Stunden
                </v-list-item-content>
                <v-list-item-avatar>
                  <v-icon
                    v-if="rapport.isFinished"
                    color="primary"
                  >
                    check_circle
                  </v-icon>
                </v-list-item-avatar>
              </v-list-item>
            </template>
          </v-list>
        </v-col>
      </v-row>
      <v-dialog
        v-model="datepicker"
        width="unset"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            v-if="$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
            slot="activator"
            fixed
            bottom
            right
            fab
            color="primary"
            v-on="on"
          >
            <v-icon>add</v-icon>
          </v-btn>
        </template>
        <v-date-picker
          v-model="newRapportDate"
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
            @click="addRapport"
          >
            OK
          </v-btn>
        </v-date-picker>
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import moment from 'moment'

export default {
  name: 'RapportOverview',
  components: {},
  data() {
    return {
      rapports: [],
      newRapportDate: new Date().toISOString().substr(0, 10),
      datepicker: false,
      isLoading: false
    }
  },
  mounted() {
    this.isLoading = true
    this.axios
      .get('rapports')
      .then(response => {
        this.rapports = response.data
        for (const rapport of this.rapports) {
          rapport.date = moment(rapport.date.date)
        }
      })
      .catch(() => {
        this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
      }).finally(() => {
        this.isLoading = false
      })
  },
  methods: {
    getFormatedWeek(date) {
      return `Woche ${date.format('W (DD.MM.YYYY')} - ${date
        .clone()
        .add(6, 'days')
        .format('DD.MM.YYYY')})`
    },
    addRapport() {
      const newRapportDate = moment(this.newRapportDate, 'YYYY-MM-DD', 'de-ch')
      this.$router.push(`/rapport/week/${newRapportDate.format('DD.MM.YYYY')}`)
    }
  }
}
</script>
