<template>
  <div class="overview">
    <v-date-picker
      v-model="date"
      first-day-of-week="1"
      :type="type"
      width="100%"
      locale="ch-de"
    ></v-date-picker>
    <p class="text-center">
      <v-btn
        :color="monthColor"
        depressed
        @click="type = 'month'"
      >
        Monat
      </v-btn>
      <v-btn
        :color="weekColor"
        depressed
        @click="type = 'date'"
      >
        Woche
      </v-btn>
    </p>
    <v-divider></v-divider>
    <div class="pa-3">
      <h2 class="mb-1">
        Bezogene Stunden {{ type === 'month' ? 'diesen Monat' : 'diese Woche' }}
      </h2>
      <p class="mb-0">
        Total: {{ totalHours }}h
      </p>
      <p>Ferien: {{ holidayHours }}h</p>
    </div>
    <p class="text-center">
      <v-btn
        color="red"
        dark
        @click="$emit('close')"
      >
        <v-icon class="mr-2">
          close
        </v-icon>Schliessen
      </v-btn>
    </p>
  </div>
</template>

<script>
export default {
  name: 'DesktopOverview',
  props: {},
  data() {
    return {
      date: new Date().toISOString().substr(0, 7),
      totalHours: 0,
      holidayHours: 0,
      type: 'month'
    }
  },
  computed: {
    monthColor() {
      return this.type === 'month' ? 'primary' : ''
    },
    weekColor() {
      return this.type === 'month' ? '' : 'primary'
    }
  },
  watch: {
    date() {
      this.getStats()
    }
  },
  mounted() {
    this.getStats()
  },
  methods: {
    getStats() {
      this.axios
        .get(`${process.env.VUE_APP_API_URL}time/stats/${this.date}?type=${this.type}`)
        .then(response => {
          this.totalHours = response.data.totalHours
          this.holidayHours = response.data.holidayHours
        })
        .catch(() => {
          this.$swal('Fehler', 'Statistiken konnten nicht geladen werden', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
.overview {
  position: absolute;
  width: 100vw;
  min-height: 100vh;
  top: 0;
  left: 0;
  background-color: white;
}

p {
  font-size: 1.2rem;
}

.close-button {
  position: absolute;
  bottom: 0;
  width: 100vw;
}
</style>
