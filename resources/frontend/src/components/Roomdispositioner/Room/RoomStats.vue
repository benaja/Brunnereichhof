<template>
  <v-row>
    <v-col
      cols="12"
      md="8"
    >
      <month-date-picker
        v-model="date"
        color="blue"
      ></month-date-picker>
    </v-col>
    <v-col
      cols="12"
      md="4"
    >
      <v-btn
        color="blue"
        class="pdf-button white--text float-right"
        depressed
        @click="generatePdf"
      >
        Pdf Generieren
      </v-btn>
    </v-col>
    <v-col cols="12">
      <v-data-table
        :headers="headers"
        :items="reservationTableItems"
        class="elevation-1"
        disable-pagination
        color="blue"
        hide-default-footer
        :custom-sort="sortItems"
        :sort-by="['entry']"
      ></v-data-table>
    </v-col>
  </v-row>
</template>

<script>
import MonthDatePicker from '@/components/Evaluation/MonthDatePicker'
import { downloadFile } from '@/utils'

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
          value: 'employeeName'
        },
        {
          text: 'Bett',
          sortable: true,
          value: 'bedName'
        }
      ]
    }
  },
  computed: {
    reservationTableItems() {
      const reservations = []
      for (const reservation of this.reservations) {
        reservations.push({
          entry: this.$moment(reservation.entry).format('DD.MM.YYYY'),
          exit: this.$moment(reservation.exit).format('DD.MM.YYYY'),
          employeeName: `${reservation.employee.lastname} ${reservation.employee.firstname}`,
          bedName: reservation.bed_room_pivot.bed.name
        })
      }
      return reservations
    }
  },
  watch: {
    date() {
      this.getReservations()
    }
  },
  mounted() {
    this.getReservations()
  },
  methods: {
    getReservations() {
      // this.axios.get(`/rooms/${this.$route.params.id}/reservations/${this.date}`).then(response => {
      //   this.reservations = response.data
      // })
    },
    sortItems(items, index, isDesc) {
      if (index.includes('entry') || index.includes('exit')) {
        items.sort((a, b) => {
          const dateA = this.$moment(a[index[0]], 'DD.MM.YYYY')
          const dateB = this.$moment(b[index[0]], 'DD.MM.YYYY')
          if (isDesc[0]) return dateB.isAfter(dateA) ? 1 : -1
          return dateA.isAfter(dateB) ? 1 : -1
        })
      } else if (index.includes('employeeName') || index.includes('bedName')) {
        items.sort((a, b) => {
          const textA = a[index[0]].toLowerCase()
          const textB = b[index[0]].toLowerCase()
          if (isDesc[0]) {
            if (textA > textB) return -1
            return textA < textB ? 1 : 0
          }
          if (textA < textB) return -1
          return textA > textB ? 1 : 0
        })
      }
      return items
    },
    generatePdf() {
      downloadFile(`pdf/rooms/${this.$route.params.id}/reservations/${this.date}`)
    }
  }
}
</script>

<style lang="scss" scoped>
.pdf-button {
  margin-top: 25px;
}
</style>
