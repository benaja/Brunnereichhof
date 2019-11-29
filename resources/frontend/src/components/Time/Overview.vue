<template>
  <div :class="['overview', {desktop: !$store.getters.isMobile}]">
    <v-date-picker
      v-model="date"
      first-day-of-week="1"
      :type="type"
      width="100%"
      locale="ch-de"
      class="elevation-0 datepicker"
    ></v-date-picker>
    <p class="text-center hidden-md-and-up">
      <v-btn :color="monthColor" depressed @click="type = 'month'">Monat</v-btn>
      <v-btn :color="weekColor" depressed @click="type = 'date'">Woche</v-btn>
    </p>
    <v-divider></v-divider>
    <div class="px-3 pt-3 hidden-md-and-up">
      <h2 class="mb-1">Bezogene Stunden {{type === 'month' ? 'diesen Monat' : 'diese Woche'}}</h2>
      <p class="mb-0">Total: {{totalHours}}h</p>
      <p class="mb-0">Ferien: {{holidayHours}}h</p>
    </div>
    <div class="px-3 pt-3 hidden-sm-and-down">
      <h2 class="mb-1">Bezogene Stunden</h2>
      <h3>Diese Woche</h3>
      <p>Total: {{totalHours}}h</p>
      <h3>Diesen Monat</h3>
      <p class="mb-0">Total: {{totalHoursMonth}}h</p>
      <p class="mb-0">Ferien: {{holidayHoursMonth}}h</p>
    </div>
    <p class="text-center hidden-md-and-up">
      <v-btn @click="$emit('close')" color="red" dark>
        <v-icon class="mr-2">close</v-icon>Schliessen
      </v-btn>
    </p>
  </div>
</template>

<script>
export default {
  name: 'Overview',
  props: {
    urlWorkerParam: String
  },
  data() {
    return {
      date: this.$store.getters.isMobile ? new Date().toISOString().substr(0, 7) : new Date().toISOString().substring(0, 10),
      totalHours: 0,
      holidayHours: 0,
      totalHoursMonth: 0,
      holidayHoursMonth: 0,
      type: window.innerWidth < 960 ? 'month' : 'date'
    }
  },
  mounted() {
    this.getStats()
  },
  methods: {
    getStats() {
      this.axios
        .get(`/time/stats/${this.date}${this.urlWorkerParam}`)
        .then(response => {
          this.totalHours = response.data.week.totalHours
          this.holidayHours = response.data.week.holidayHours
          if (response.data.month) {
            this.totalHoursMonth = response.data.month.totalHours
            this.holidayHoursMonth = response.data.month.holidayHours
          }
        })
        .catch(() => {
          this.$swal('Fehler', 'Statistiken konnten nicht geladen werden', 'error')
        })
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
      this.$emit('change', this.date)
      this.getStats()
    },
    type() {
      // if (this.type === 'date') this.date = new Date().toISOString().substr(0, 10)
    }
  }
}
</script>

<style lang="scss" scoped>
.overview {
  position: absolute;
  width: 100%;
  min-height: 100%;
  top: 0;
  left: 0;
  background-color: white;
  box-shadow: lightgray 0 0 10px;

  .datepicker {
    margin-top: 56px;
  }

  &.desktop {
    .datepicker {
      margin-top: 0;
    }
  }
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
