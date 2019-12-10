<template>
  <div>
    <month-date-picker v-model="date" color="blue"></month-date-picker>
    <v-data-table
      :headers="headers"
      :items="reservationTableItems"
      class="elevation-1"
      disable-pagination
      color="blue"
    ></v-data-table>
  </div>
</template>

<script>
import MonthDatePicker from '@/components/Evaluation/MonthDatePicker'

export default {
  components: {
    MonthDatePicker
  },
  data() {
    return {
      reservations: [],
      date: `month/${this.$moment().format('YYYY-MM')}`,
      headers: [
        {
          text: 'Eintritt',
          sortable: true,
          value: 'entry'
        },
        {
          text: 'Austritt',
          sortable: true,
          value: 'exit'
        },
        {
          text: 'Mitarbeiter',
          sortable: true,
          value: 'employee.name'
        },
        {
          text: 'Bett',
          sortable: true,
          value: 'bed.name'
        }
      ]
    }
  },
  computed: {
    reservationTableItems() {
      let reservations = []
      for (let reservation of this.reservations) {
        reservations.push({
          entry: this.$moment(reservation.entry).format('DD.MM.YYYY'),
          exit: this.$moment(reservation.exit).format('DD.MM.YYYY'),
          employee: {
            name: `${reservation.employee.lastname} ${reservation.employee.firstname}`
          },
          bed: {
            name: reservation.bed_room_pivot.bed.name
          }
        })
      }
      return reservations
    }
  },
  mounted() {
    this.getReservations()
  },
  methods: {
    getReservations() {
      let date = this.date || `month/${this.$moment().format('YYYY-MM')}`
      this.axios.get(`/rooms/${this.$route.params.id}/reservations/${this.date}`).then(response => {
        this.reservations = response.data
      })
    }
  },
  watch: {
    date() {
      this.getReservations()
    }
  }
}
</script>

<style>
</style>
