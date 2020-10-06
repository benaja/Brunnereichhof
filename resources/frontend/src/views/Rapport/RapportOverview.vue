<template>
  <fragment>
    <navigation-bar
      title="Rapportübersicht"
      :loading="isLoading"
    ></navigation-bar>
    <v-container>
      <v-data-table
        :items="rapports"
        :headers="headers"
        :items-per-page="15"
        :server-items-length="meta.total || rapports.length"
        :footer-props=" {itemsPerPageOptions: [15, 30, -1]}"
        @pagination="paginate"
      >
        <template v-slot:item="{item}">
          <router-link
            tag="tr"
            class="rapport-list-item"
            :to="'/rapport/week/' + item.date.format('DD.MM.YYYY')"
          >
            <td>{{ item.date.format('W') }}</td>
            <td>{{ getFormatedWeek(item.date) }}</td>
            <td>{{ item.hours }}</td>
            <td>
              <v-icon
                v-if="item.isFinished"
                color="primary"
              >
                check_circle
              </v-icon>
            </td>
          </router-link>
        </template>
      </v-data-table>
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
      isLoading: false,
      meta: {},
      headers: [
        {
          text: 'Woche'
        },
        {
          text: 'Datum'
        },
        {
          text: 'Stunden'
        },
        {
          text: 'Abgeschlossen'
        }
      ]
    }
  },
  methods: {
    paginate(pagination) {
      this.isLoading = true
      this.axios
        .get('rapports', {
          params: {
            page: pagination.page,
            per_page: pagination.itemsPerPage
          }
        })
        .then(response => {
          this.rapports = response.data.data.map(r => ({
            ...r,
            date: moment(r.date.date)
          }))
          this.meta = response.data.meta || {}
        })
        .catch(() => {
          this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        }).finally(() => {
          this.isLoading = false
        })
    },
    getFormatedWeek(date) {
      return `${date.format('DD.MM.YYYY')} - ${date
        .clone()
        .add(6, 'days')
        .format('DD.MM.YYYY')}`
    },
    addRapport() {
      const newRapportDate = moment(this.newRapportDate, 'YYYY-MM-DD', 'de-ch')
      this.$router.push(`/rapport/week/${newRapportDate.format('DD.MM.YYYY')}`)
    }
  }
}
</script>

<style lang="scss" scoped>
.rapport-list-item {
  cursor: pointer;
}
</style>
