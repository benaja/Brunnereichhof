<template>
  <v-container>
    <h1>Auswertung</h1>
    <v-radio-group
      v-model="dateType"
      row
    >
      <v-radio
        label="Woche"
        value="date"
        color="blue"
      ></v-radio>
      <v-radio
        label="Monat"
        value="month"
        color="blue"
      ></v-radio>
      <v-radio
        label="Jahr"
        value="year"
        color="blue"
      ></v-radio>
    </v-radio-group>
    <date-picker
      v-model="date"
      :type="dateType"
      label="Datum"
      outlined
      color="blue"
      @input="updateStats"
    ></date-picker>

    <v-tabs
      v-model="tab"
      color="blue"
    >
      <v-tab>Übernachtungen</v-tab>
      <v-tab>Einquartierungen</v-tab>
      <v-tab>Zimmerwechsel</v-tab>
    </v-tabs>
    <progress-linear
      :loading="isLoading.rooms || isLoadingStats"
      indeterminate
      color="blue"
    ></progress-linear>

    <v-tabs-items v-model="tab">
      <v-tab-item>
        <p class="subtitle-1 mt-3">
          Wähle die gewünschten Zimmer aus
        </p>
        <v-row>
          <v-col
            cols="12"
            md="6"
            lg="4"
          >
            <v-checkbox
              v-model="selectAllRooms"
              label="Alle auswählen"
              color="blue"
              class="pt-0 mt-0"
            ></v-checkbox>
          </v-col>
          <v-col
            v-for="room of rooms"
            :key="room.id"
            cols="12"
            md="6"
            lg="4"
          >
            <v-checkbox
              v-model="selectedRooms"
              :label="`${room.name} | ${room.number}`"
              :value="room.id"
              class="pt-0 mt-0"
              color="blue"
            ></v-checkbox>
          </v-col>
        </v-row>
        <v-btn
          :loading="isLoadingPdf"
          :disabled="!selectedRooms.length"
          depressed
          color="blue"
          class="white--text"
          @click="generateRoomPdf"
        >
          <v-icon class="mr-2">
            picture_as_pdf
          </v-icon>PDF generieren
        </v-btn>
      </v-tab-item>
      <v-tab-item>
        <p class="headline">
          {{ quartering }}
        </p>
      </v-tab-item>
      <v-tab-item>
        <p class="headline">
          {{ roomChanges }}
        </p>
      </v-tab-item>
    </v-tabs-items>
  </v-container>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import { mapGetters } from 'vuex'
import { downloadFile } from '@/utils'

export default {
  components: {
    DatePicker
  },
  data() {
    return {
      dateType: 'year',
      date: this.$moment().format('YYYY-MM-DD'),
      tab: 0,
      selectedRooms: [],
      isLoadingPdf: false,
      isLoadingStats: false,
      quartering: 0,
      roomChanges: 0
    }
  },
  computed: {
    ...mapGetters(['rooms', 'isLoading']),
    selectAllRooms: {
      get() {
        return this.rooms.length === this.selectedRooms.length
      },
      set(value) {
        if (value) {
          this.selectedRooms = this.rooms.map(r => r.id)
        } else {
          this.selectedRooms = []
        }
      }
    }
  },
  watch: {
    tab() {
      this.updateStats()
    },
    dateType() {
      this.updateStats()
    }
  },
  mounted() {
    this.$store.dispatch('fetchRooms')
  },
  methods: {
    generateRoomPdf() {
      this.isLoadingPdf = true
      downloadFile('pdf/sleep-over/rooms', { rooms: this.selectedRooms, type: this.dateType, date: this.date })
        .catch(() => {
          this.$store.dispatch('error', 'Fehler bei der Erstellung des PDFs.')
        })
        .finally(() => {
          this.isLoadingPdf = false
        })
    },
    async updateStats() {
      if (this.tab === 1) {
        this.quartering = await this.getStats('/stats/quartering', 'Einquartierungen')
      } else if (this.tab === 2) {
        this.roomChanges = await this.getStats('/stats/room-changes', 'Zimmerwechsel')
      }
    },
    getStats(url, name) {
      return new Promise(resolve => {
        this.isLoadingStats = true
        this.axios
          .get(url, {
            params: {
              type: this.dateType,
              date: this.date
            }
          })
          .then(response => {
            resolve(response.data)
          })
          .catch(() => {
            this.$store.dispatch('error', `Fehler beim Apruffen der ${name}`)
          })
          .finally(() => {
            this.isLoadingStats = false
          })
      })
    }
  }
}
</script>

<style>
</style>
