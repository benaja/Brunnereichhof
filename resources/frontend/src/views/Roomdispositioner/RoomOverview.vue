<template>
  <fragment>
    <navigation-bar
      title="Raumübersicht"
      color="blue"
      :loading="isLoading"
      full-width
    >
      <v-switch
        v-model="onlyActiveRooms"
        class="ml-auto mr-4"
        color="blue"
        label="Nur aktive Räume"
      ></v-switch>
      <v-switch
        v-model="showFreeBeds"
        color="blue"
        label="Freie Betten anzeigen"
      ></v-switch>
    </navigation-bar>
    <v-container fluid>
      <v-row>
        <v-col
          cols="12"
          md="5"
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
              depressed
              :loading="isLoadingPdf"
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
          <div
            v-for="room of rooms"
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
    </v-container>
  </fragment>
</template>

<script>
import { downloadFile } from '@/utils'

export default {
  data() {
    return {
      date: this.$moment(new Date()).format('YYYY-MM-DD'),
      roomsWithReservations: [],
      showFreeBeds: JSON.parse(localStorage.getItem('showFreeBeds')),
      isLoading: false,
      isLoadingPdf: false,
      onlyActiveRooms: true
    }
  },
  computed: {
    rooms() {
      const selectedDate = this.$moment(this.date)
      return this.roomsWithReservations
        .filter(r => !this.onlyActiveRooms
          || r.active_history.find(a => this.$moment(a.active_from).isBefore(selectedDate)
            && (!a.active_to || this.$moment(a.active_to).isAfter(selectedDate))))
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
      this.isLoading = true
      this.axios
        .get(`/rooms/reservations/${this.date}`)
        .then(response => {
          this.roomsWithReservations = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Daten konnten nicht abgeruffen werden. Bitte versuchen Sie es später erneut.', 'error')
        }).finally(() => {
          this.isLoading = false
        })
    },
    generatePdf() {
      this.isLoadingPdf = true
      downloadFile(`rooms/evaluation/${this.date}/pdf?showFreeBeds=${this.showFreeBeds}`).catch(() => {
        this.$store.commit('error', 'Fehler beim Generieren des PDFs')
      }).finally(() => {
        this.isLoadingPdf = false
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.datepicker {
  margin: 20px;
}
</style>
