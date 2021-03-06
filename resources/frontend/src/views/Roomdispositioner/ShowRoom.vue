<template>
  <fragment>
    <navigation-bar
      title="Zimmer anzeigen"
      color="blue"
      :loading="$store.getters.isLoading.rooms"
      full-width
    >
    </navigation-bar>
    <v-container>
      <v-row>
        <v-col
          xs12
          md6
        >
          <v-autocomplete
            v-model="selectedRoom"
            :items="activeRooms"
            outlined
            label="Raum"
            item-value="id"
            item-text="name"
            color="blue"
            item-color="blue"
            @input="getReservations"
          ></v-autocomplete>
        </v-col>
        <v-col
          xs12
          md6
        >
          <date-picker
            v-model="selectedYear"
            type="year"
            outlined
            label="Jahr"
            color="blue"
            @input="getReservations"
          ></date-picker>
        </v-col>
      </v-row>
      <v-data-table
        :headers="headers"
        :loading="isLoadingReservations"
        :items="reservationTableItems"
        class="elevation-1"
        disable-pagination
        color="blue"
        hide-default-footer
        :custom-sort="sortItems"
        :sort-by="['entry']"
        not-data-text="Keine Einträge vorhanden"
      >
        <template v-slot:item="{item}">
          <v-menu
            v-model="item.model"
            :close-on-content-click="false"
            z-index="4"
            max-width="100%"
            min-width="300"
            close-delay="10"
          >
            <template v-slot:activator="{on}">
              <tr
                class="row-item"
                :class="{'blue lighten-5': item.active}"
                v-on="on"
              >
                <td>{{ item.entry }}</td>
                <td>{{ item.exit }}</td>
                <td>{{ item.employeeName }}</td>
                <td>{{ item.bedName }}</td>
              </tr>
            </template>
            <reservation-details
              v-model="item.reservation"
              :original="item.original"
              :selected-day="$moment(item.entry, 'DD.MM.YYYY')"
              @close="item.model = false"
              @delete="deleteReservation"
              @update="getReservations"
              @updateAll="getReservations"
            />
          </v-menu>
        </template>
      </v-data-table>
    </v-container>
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'
import DatePicker from '@/components/general/DatePicker'
import ReservationDetails from '@/components/Roomdispositioner/ReservationDetails'

export default {
  components: {
    DatePicker,
    ReservationDetails
  },
  data() {
    return {
      selectedRoom: null,
      selectedYear: this.$moment().format('YYYY'),
      reservations: [],
      isLoadingReservations: false,
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
    ...mapGetters(['activeRooms']),
    reservationTableItems() {
      return this.reservations.map(reservation => this.mapReservation(reservation))
    }
  },
  mounted() {
    this.$store.dispatch('fetchRooms')
  },
  methods: {
    getReservations() {
      this.isLoadingReservations = true
      this.axios.get(`/rooms/${this.selectedRoom}/reservations`, {
        params: { dateRangeType: 'year', date: this.selectedYear }
      }).then(response => {
        this.reservations = response.data
      }).catch(() => {
        this.$store.dispatch('error', 'Raum-Statistiken konnten nicht geladen werden')
      }).finally(() => {
        this.isLoadingReservations = false
      })
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
    mapReservation(reservation) {
      return {
        id: reservation.id,
        entry: this.$moment(reservation.entry).format('DD.MM.YYYY'),
        exit: this.$moment(reservation.exit).format('DD.MM.YYYY'),
        employeeName: `${reservation.employee.lastname} ${reservation.employee.firstname}`,
        bedName: reservation.bed_room_pivot.bed.name,
        active: this.$moment(reservation.entry).isSameOrBefore(this.$moment(), 'day')
            && this.$moment(reservation.exit).isSameOrAfter(this.$moment(), 'day'),
        reservation: this._.cloneDeep(reservation),
        original: this._.cloneDeep(reservation),
        model: false
      }
    },
    deleteReservation(r) {
      const reservation = this.reservations.find(res => res.id === r.id)
      this.reservations.splice(this.reservations.indexOf(reservation), 1)
    },
    updateReservation(originalReservation, reservation) {
      this.reservations = this.reservations.map(r => {
        if (r.id !== originalReservation.id) return r
        return reservation
      })
    },
    updateAllReservations(reservations) {
      this.reservations = reservations
    }
  }
}
</script>

<style lang="scss" scoped>
.row-item {
  cursor: pointer;
}
</style>
