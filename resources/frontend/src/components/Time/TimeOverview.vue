<template>
  <transition name="move">
    <div
      v-if="value || !$vuetify.breakpoint.smAndDown"
      :class="['overview', { desktop: !$vuetify.breakpoint.smAndDown }]"
    >
      <div class="header py-3 hidden-md-and-up">
        <v-btn
          icon
          class="ml-3"
          @click="$emit('input', false)"
        >
          <v-icon>close</v-icon>
        </v-btn>
      </div>
      <v-divider class="hidden-md-and-up"></v-divider>
      <div class="content">
        <v-date-picker
          v-model="date"
          first-day-of-week="1"
          :type="type"
          width="100%"
          locale="ch-de"
          class="elevation-0 datepicker"
          show-week
          no-title
        ></v-date-picker>
        <p class="text-center hidden-md-and-up">
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
        <progress-linear
          v-if="$vuetify.breakpoint.smAndDown"
          :loading="isLoading"
        ></progress-linear>
        <div class="px-3 pt-3 hidden-md-and-up">
          <h2 class="mb-1">
            Bezogene Stunden {{ type === 'month' ? 'diesen Monat' : 'diese Woche' }}
          </h2>
          <p class="mb-0">
            Total: {{ totalHours }}h
          </p>
          <p class="mb-0">
            Ferien: {{ holidayHours }}h
          </p>
        </div>
        <div class="pl-6 pt-3 hidden-sm-and-down">
          <h2 class="mb-1">
            Bezogene Stunden
          </h2>
          <h3>Diese Woche</h3>
          <p>Total: {{ totalHours }}h</p>
          <h3>Diesen Monat</h3>
          <p class="mb-0">
            Total: {{ totalHoursMonth }}h
          </p>
          <p class="mb-0">
            Ferien: {{ holidayHoursMonth }}h
          </p>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    urlWorkerParam: {
      type: String,
      default: null
    },
    value: Boolean
  },
  data() {
    return {
      date: this.$store.getters.isMobile ? this.$moment().format('YYYY-MM')
        : this.$moment().format('YYYY-MM-DD'),
      totalHours: 0,
      holidayHours: 0,
      totalHoursMonth: 0,
      holidayHoursMonth: 0,
      type: window.innerWidth < 960 ? 'month' : 'date',
      isLoading: false
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
    }
  },
  mounted() {
    this.getStats()
  },
  methods: {
    getStats() {
      this.isLoading = true
      this.axios
        .get(`/times/stats/${this.date}${this.urlWorkerParam}`)
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
        }).finally(() => {
          this.isLoading = false
        })
    }
  }
}
</script>

<style lang="scss" scoped>
.overview {
  position: fixed;
  width: 100%;
  min-height: 100%;
  top: 0;
  left: 0;
  background-color: white;
  box-shadow: lightgray 0 0 10px;
  z-index: 10;

  &.desktop {
    z-index: 1;
    position: absolute;

    .datepicker {
      margin-top: 0;
    }
  }
}

.header {
  height: 60px;
}

.content {
  max-height: calc(100vh - 61px);
  max-height: calc(var(--vh, 1vh) * 100 - 61px);
  overflow-y: auto;
}

p {
  font-size: 1.2rem;
}
.move-enter-active,
.move-leave-active {
  transition: translateY(0);
  transition-duration: 0.3s;
}
.move-enter, .move-leave-to /* .fade-leave-active below version 2.1.8 */ {
  transform: translateY(130vh);
}
</style>
