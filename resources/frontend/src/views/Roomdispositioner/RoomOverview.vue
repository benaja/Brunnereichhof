<template>
  <fragment>
    <navigation-bar title="Raumübersicht"></navigation-bar>
    <v-row>
      <v-col
        class="side-container"
        cols="12"
        md="4"
        lg="3"
      >
        <v-date-picker
          v-model="date"
          class="datepicker"
          no-title
          locale="ch-de"
          first-day-of-week="1"
          color="blue"
          width="100%"
          show-week
        ></v-date-picker>
        <diV class="text-center">
          <v-btn
            color="blue"
            class="white--text text-center"
            @click="generatePdf"
          >
            PDF
            <v-icon right>
              picture_as_pdf
            </v-icon>
          </v-btn>
        </diV>
      </v-col>
      <v-col class="content-container">
        <v-switch
          v-model="showFreeBeds"
          class="float-right mt-2"
          color="blue"
          label="Freie Betten anzeigen"
        ></v-switch>
        <div
          v-for="room of roomsWithReservations"
          :key="'room-' + room.id"
          class="mb-3"
        >
          <h3>{{ room.number }} / {{ room.name }} ({{ room.location }})</h3>
          <template
            v-for="(reservation, index) of room.bedsWithReservation"
            class="mb-1"
          >
            <p
              v-if="reservation.employee"
              :key="'reservation-' + index"
              class="mb-1"
            >
              {{ reservation.employee.lastname }} {{ reservation.employee.firstname }}
              ({{ reservation.bed.name }})
            </p>
            <p
              v-else-if="showFreeBeds"
              :key="'reservation-' + index"
              class="mb-1 blue--text text--darken-2"
            >
              {{ reservation.bedName }} (Freie Plätze: {{ reservation.freePlaces }})
            </p>
          </template>
        </div>
      </v-col>
    </v-row>
  </fragment>
</template>

<script>
import { downloadFile } from '@/utils'

export default {
  name: 'RoomdispositionerEvaluation',
  data() {
    return {
      date: this.$moment(new Date()).format('YYYY-MM-DD'),
      roomsWithReservations: [],
      showFreeBeds: JSON.parse(localStorage.getItem('showFreeBeds'))
    }
  },
  watch: {
    date() {
      this.getReservationsByDate()
    },
    showFreeBeds() {
      localStorage.setItem('showFreeBeds', JSON.stringify(this.showFreeBeds))
    }
  },
  mounted() {
    this.getReservationsByDate()
  },
  methods: {
    getReservationsByDate() {
      this.$store.commit('isLoading', true)
      this.axios
        .get(`/rooms/reservations/${this.date}`)
        .then(response => {
          this.$store.commit('isLoading', false)
          this.roomsWithReservations = response.data
        })
        .catch(() => {
          this.$store.commit('isLoading', false)
          this.$swal('Fehler', 'Daten konnten nicht abgeruffen werden. Bitte versuchen Sie es später erneut.', 'error')
        })
    },
    generatePdf() {
      downloadFile(`rooms/evaluation/${this.date}/pdf?showFreeBeds=${this.showFreeBeds}`)
    }
  }
}
</script>

<style lang="scss" scoped>
.datepicker {
  margin: 20px;
}
.side-container {
  width: 340px;
  // min-height: calc(100vh - 64px);
  // height: 100%;
}

.content-container {
  width: calc(100% - 340px);
  padding: 20px;
}

@media only screen and (max-width: 600px) {
  .side-container {
    width: 100%;
    height: auto;
  }

  .content-container {
    width: 100%;
  }
}
</style>
