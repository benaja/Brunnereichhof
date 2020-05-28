<template>
  <div class="time-container">
    <!-- mobile -->
    <v-row
      v-if="$vuetify.breakpoint.smAndDown"
      wrap
      class="pa-0 ma-0"
    >
      <v-col
        cols="12"
        class="pa-0"
      >
        <progress-linear :loading="isLoading"></progress-linear>
      </v-col>
      <v-col cols="2">
        <p class="mx-0 mt-4 text-center">
          <v-btn
            text
            icon
            @click="previousDay"
          >
            <v-icon>keyboard_arrow_left</v-icon>
          </v-btn>
        </p>
      </v-col>
      <v-col cols="8">
        <v-dialog
          v-model="dateDialog"
          width="290px"
          class="text-center"
        >
          <template v-slot:activator="{ on }">
            <div
              class="mobile-day-label"
              v-on="on"
            >
              <p class="text-center overline mb-0">
                {{ $moment(date).format('dddd') }}
              </p>
              <p class="text-center display-1 mb-0">
                {{ $moment(date).format('DD') }}
              </p>
              <p class="text-center overline mb-0">
                {{ $moment(date).format('MMM') }}
              </p>
            </div>
          </template>
          <v-date-picker
            v-model="date"
            scrollable
            first-day-of-week="1"
            locale="ch-de"
            show-week
            @change="dateDialog = false"
          ></v-date-picker>
        </v-dialog>
      </v-col>
      <v-col cols="2">
        <p class="mx-0 mt-4 text-center">
          <v-btn
            text
            icon
            @click="nextDay"
          >
            <v-icon>keyboard_arrow_right</v-icon>
          </v-btn>
        </p>
      </v-col>
      <v-col
        cols="12"
        class="pa-0"
      >
        <v-divider></v-divider>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
      >
        <v-row
          ref="scrollContainer"
          class="scroll-container"
          :style="{ overflowY: isScrolling ? 'hidden' : 'auto' }"
        >
          <v-col
            cols="2"
            class="time-numbers py-0"
          >
            <div
              v-for="index in 23"
              :key="index"
              class="time-number"
            >
              <p class="text-right caption">
                {{ timeString(index) }}
              </p>
            </div>
          </v-col>
          <v-col
            cols="10"
            class="py-0 pr-0"
          >
            <div
              v-touch:swipe.left="nextDay"
              v-touch:swipe.right="previousDay"
              class="days"
            >
              <div class="lines">
                <div
                  v-for="index in 24"
                  :key="index"
                  class="time-number"
                >
                  <v-divider v-if="index !== 1"></v-divider>
                </div>
              </div>
              <day
                v-if="weekDays.length > 0"
                v-model="weekDays[0].hours"
                :date="date"
                :settings="settings"
                :url-worker-param="urlWorkerParam"
                @update="props => openTimePopup({ day: weekDays[0], ...props })"
                @openOveriew="isOverviewOpen = true"
                @scrolling="scrolling => (isScrolling = scrolling)"
              ></day>
            </div>
          </v-col>
        </v-row>
      </v-col>
      <time-overview
        v-model="isOverviewOpen"
        :url-worker-param="urlWorkerParam"
      ></time-overview>
      <time-popup
        ref="timeCard"
        :url-worker-param="urlWorkerParam"
      ></time-popup>
    </v-row>
    <!-- desktop -->
    <v-row
      v-else
      wrap
    >
      <v-col
        cols="3"
        xl="2"
        class="py-0 px-0"
      >
        <div class="overview-container">
          <time-overview
            ref="overview"
            :url-worker-param="urlWorkerParam"
            @change="newDate => (date = newDate)"
          ></time-overview>
        </div>
      </v-col>
      <v-col
        cols="9"
        xl="10"
        class="py-0"
      >
        <progress-linear :loading="isLoading"></progress-linear>
        <v-row>
          <v-col
            cols="11"
            offset="1"
            class="day-labels"
          >
            <div
              v-for="(day, index) of weekDays"
              :key="index"
              class="day-label"
            >
              <p class="text-center overline mb-0">
                {{ $moment(day.date).format('dd') }}
              </p>
              <div>
                <p
                  class="text-center display-1 mb-0"
                  :class="{'primary--text': $moment(day.date).isSame($moment(), 'day')}"
                >
                  {{ $moment(day.date).format('DD') }}
                </p>
              </div>
            </div>
          </v-col>
          <v-col
            cols="11"
            offset="1"
            class="pa-0"
          >
            <v-divider></v-divider>
          </v-col>
        </v-row>
        <v-row
          ref="scrollContainer"
          class="scroll-container"
        >
          <time-card
            ref="timeCard"
            :url-worker-param="urlWorkerParam"
            @updated="$refs.overview.getStats()"
          ></time-card>
          <v-col
            cols="1"
            class="time-numbers py-0"
          >
            <div
              v-for="index in 23"
              :key="index"
              class="time-number"
            >
              <p class="text-right">
                {{ timeString(index) }}
              </p>
            </div>
          </v-col>
          <v-col
            cols="11"
            class="py-0 pr-0"
          >
            <div class="days">
              <div class="lines">
                <div
                  v-for="index in 24"
                  :key="index"
                  class="time-number"
                >
                  <v-divider v-if="index !== 1"></v-divider>
                </div>
              </div>
              <template v-for="(day, index) of weekDays">
                <day
                  :key="index"
                  v-model="day.hours"
                  :date="new Date(day.date).toISOString().substr(0, 10)"
                  :settings="settings"
                  :url-worker-param="urlWorkerParam"
                  @update="props => openTimePopup({ day, index, ...props })"
                ></day>
              </template>
            </div>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import Day from '@/components/Time/Day'
import TimeOverview from '@/components/Time/TimeOverview'
import TimePopup from '@/components/Time/TimePopup'
import TimeCard from '@/components/Time/TimeCard'
import { mapGetters } from 'vuex'

export default {
  name: 'TimeView',
  components: {
    Day,
    TimeOverview,
    TimePopup,
    TimeCard
  },
  props: {
    workerId: {
      type: [Number, String],
      default: null
    }
  },
  data() {
    return {
      dateDialog: false,
      date: this.$moment().format('YYYY-MM-DD'),
      isOverviewOpen: false,
      weekDays: [],
      timePopupForm: {
        isOpen: false,
        date: '',
        day: {},
        timerecord: {}
      },
      isScrolling: false,
      isLoading: false
    }
  },
  computed: {
    ...mapGetters({
      settings: 'timerecordSettings'
    }),
    urlWorkerParam() {
      if (this.workerId) return `?workerId=${this.workerId}`
      return ' '
    },
    formatedDate() {
      return this.$moment(this.date, 'YYYY-MM-DD').format('DD.MM.YYYY')
    }
  },
  watch: {
    date() {
      this.getDay()
    }
  },
  mounted() {
    this.$store.dispatch('fetchTimerecordSettings', this.urlWorkerParam)
    this.$store.dispatch('fetchWorktypes')
    this.getDay()
    this.$refs.scrollContainer.scroll(0, 270)
  },
  methods: {
    nextDay() {
      const tomorrow = this.$moment(this.date, 'YYYY-MM-DD').add(1, 'day')
      this.date = tomorrow.format('YYYY-MM-DD')
    },
    previousDay() {
      const yesterday = this.$moment(this.date, 'YYYY-MM-DD').subtract(1, 'day')
      this.date = yesterday.format('YYYY-MM-DD')
    },
    getDay() {
      let url = ''
      if (this.$store.getters.isMobile) {
        url = `/times/${this.date}`
      } else {
        url = `/times/week/${this.date}`
      }
      this.isLoading = true
      this.axios
        .get(url + this.urlWorkerParam)
        .then(response => {
          this.weekDays = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Daten konnten nicht abgeruffen werden', 'error')
        })
        .finally(() => {
          this.isLoading = false
        })
    },
    openTimePopup(props) {
      this.$refs.timeCard.openNewTimerecord(props)
    },
    timeString(index) {
      return index < 10 ? `0${index}:00` : `${index}:00`
    }
  }
}
</script>

<style lang="scss" scoped>
.time-number {
  height: 50px;
  position: relative;

  p {
    position: absolute;
    margin: 0;
    bottom: -0.5em;
    right: 0;
    line-height: 1em;
  }
}

.overview-container {
  position: relative;
  width: 100%;
  height: calc(100vh - 68px);
  max-height: calc(var(--vh, 1vh) * 100 - 68px);
}

.scroll-container {
  max-height: calc(100vh - 142px);
  max-height: calc(var(--vh, 1vh) * 100 - 142px);
  overflow-y: scroll;
  position: relative;
}

.days {
  display: flex;
  flex-wrap: wrap;
  position: relative;
}

.day-labels {
  display: flex;
}

.day-label {
  width: calc(100% / 7.01);
  height: 46px;
}

.lines {
  position: absolute;
  width: calc(100% + 10px);
  height: 100%;
  top: 0;
  left: -10px;
}

.day-name {
  margin-top: 10px;
  margin-bottom: -12px;
}

.mobile-day-label {
  height: 70px;
}

@media only screen and (max-width: 960px) {
  .scroll-container {
    max-height: calc(100vh - 164px);
    max-height: calc(var(--vh, 1vh) * 100 - 164px);
  }
}
</style>

<style lang="scss">
.text-center {
  input {
    text-align: center;
  }
}
</style>
