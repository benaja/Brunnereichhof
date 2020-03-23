<template>
  <div class="time-container">
    <v-row wrap v-if="$store.getters.isMobile">
      <v-col cols="12">
        <p class="text-center mt-0 font-weight-bold day-name">{{ dayName }}</p>
      </v-col>
      <v-col cols="2">
        <p class="ma-0 text-center">
          <v-btn text icon @click="previousDay">
            <v-icon>keyboard_arrow_left</v-icon>
          </v-btn>
        </p>
      </v-col>
      <v-col cols="8" class="text-center">
        <v-dialog v-model="dateDialog" width="290px" class="text-center">
          <template v-slot:activator="{ on }">
            <v-text-field v-on="on" v-model="formatedDate" readonly class="text-center pt-0"></v-text-field>
          </template>
          <v-date-picker
            v-model="date"
            scrollable
            first-day-of-week="1"
            locale="ch-de"
            @change="dateDialog = false"
            show-week
          ></v-date-picker>
        </v-dialog>
      </v-col>
      <v-col cols="2">
        <p class="ma-0 text-center">
          <v-btn text icon @click="nextDay">
            <v-icon>keyboard_arrow_right</v-icon>
          </v-btn>
        </p>
      </v-col>
      <v-col cols="2">
        <div v-for="index in 24" :key="index" class="time">
          <p class="text-right pr-3">{{ index }}:00</p>
        </div>
      </v-col>
      <v-col cols="10">
        <v-touch @swipeleft="nextDay" @swiperight="previousDay">
          <day
            v-if="weekDays.length > 0"
            :date="date"
            v-model="weekDays[0].hours"
            :settings="settings"
            @add="date => openTimePopup({}, weekDays[0])"
            @edit="timerecord => openTimePopup(timerecord, weekDays[0])"
            @openOveriew="isOverviewOpen = true"
            :url-worker-param="urlWorkerParam"
          ></day>
        </v-touch>
      </v-col>
      <overview
        v-if="isOverviewOpen"
        @close="isOverviewOpen = false"
        :url-worker-param="urlWorkerParam"
      ></overview>
      <time-popup
        v-model="timePopupForm.isOpen"
        :date="timePopupForm.date"
        :settings="settings"
        :timerecord="timePopupForm.timerecord"
        @add="
          newTimerecords => {
            timePopupForm.day.hours = newTimerecords
          }
        "
        :url-worker-param="urlWorkerParam"
      />
    </v-row>
    <v-row v-else wrap>
      <v-col cols="3" xl="2" class="py-0 px-0">
        <div class="overview-container">
          <overview
            @change="newDate => (date = newDate)"
            ref="overview"
            :url-worker-param="urlWorkerParam"
          ></overview>
          <time-popup
            v-model="timePopupForm.isOpen"
            :date="timePopupForm.date"
            :settings="settings"
            :timerecord="timePopupForm.timerecord"
            @add="
              newTimerecords => {
                timePopupForm.day.hours = newTimerecords
                $refs.overview.getStats()
              }
            "
            :url-worker-param="urlWorkerParam"
          />
        </div>
      </v-col>
      <v-col cols="9" xl="10" class="py-0">
        <v-row>
          <v-col cols="11" offset="1" class="day-labels">
            <div class="day-label" v-for="(day, index) of weekDays" :key="index">
              <p class="text-center overline">{{ $moment(day.date).format('dd') }}</p>
              <p class="text-center display-1">{{ $moment(day.date).format('DD')}}</p>
            </div>
          </v-col>
        </v-row>
        <v-row class="scroll-container">
          <v-col cols="1" class="time-numbers py-0">
            <div v-for="index in 23" :key="index" class="time-number">
              <p class="text-right">{{ timeString(index)}}</p>
            </div>
          </v-col>
          <v-col cols="11" class="py-0 pr-0">
            <div class="days">
              <div class="lines">
                <div v-for="index in 24" :key="index" class="time-number">
                  <v-divider></v-divider>
                </div>
              </div>
              <template v-for="(day, index) of weekDays">
                <day
                  :key="index"
                  :date="new Date(day.date).toISOString().substr(0, 10)"
                  v-model="day.hours"
                  :settings="settings"
                  @update="$refs.overview.getStats()"
                  @add="date => openTimePopup({}, day)"
                  @edit="timerecord => openTimePopup(timerecord, day)"
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
import Overview from '@/components/Time/Overview'
import TimePopup from '@/components/Time/TimePopup'

export default {
  name: 'TimeView',
  components: {
    Day,
    Overview,
    TimePopup
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
      date: '',
      numbers: [3, 4, 5, 6, 7, 8, 9, 10, 11],
      settings: {},
      isOverviewOpen: false,
      weekDays: [],
      timePopupForm: {
        isOpen: false,
        date: '',
        day: {},
        timerecord: {}
      }
    }
  },
  mounted() {
    this.axios
      .get(`/settings/time${this.urlWorkerParam}`)
      .then(response => {
        this.settings = response.data
      })
      .catch(() => {
        this.$swal('Fehler', 'Einstellungen konnten nicht abgeruffen werden', 'error')
      })
    this.date = new Date().toISOString().substr(0, 10)
    this.getDay()
  },
  methods: {
    nextDay() {
      let tomorrow = new Date(this.date)
      tomorrow.setDate(tomorrow.getDate() + 1)
      this.date = tomorrow.toISOString().substring(0, 10)
    },
    previousDay() {
      let yesterday = new Date(this.date)
      yesterday.setDate(yesterday.getDate() - 1)
      this.date = yesterday.toISOString().substring(0, 10)
    },
    openAddTimePopup(date) {
      this.isAddTimeOpen = true
    },
    getDay() {
      let url = ''
      if (this.$store.getters.isMobile) {
        url = '/time/' + this.date
      } else {
        url = '/time/week/' + this.date
      }
      this.axios
        .get(url + this.urlWorkerParam)
        .then(response => {
          this.weekDays = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Daten konnten nicht abgeruffen werden', 'error')
        })
    },
    openTimePopup(timerecord, day) {
      this.timePopupForm.timerecord = timerecord
      this.timePopupForm.isOpen = true
      this.timePopupForm.day = day
      this.timePopupForm.date = day.date
    },
    timeString(index) {
      return index < 10 ? `0${index}:00` : `${index}:00`
    }
  },
  computed: {
    dayName() {
      let date = new Date(this.date)
      // set sunday as last day of month
      let day = date.getDay() === 0 ? 7 : date.getDay()
      day--
      return this.$store.getters.dayNames[day]
    },
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
  }
}
</script>

<style lang="scss" scoped>
.time-container {
  padding: 0 5px;
}

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
  height: calc(100vh - 64px);
}

.scroll-container {
  max-height: calc(100vh - 64px);
  overflow-y: scroll;
}

.days {
  display: flex;
  flex-wrap: wrap;
  margin-top: 70px;
  position: relative;

  // > div {
  //   width: calc(100% / 7.01 - 1px);
  // }
}

.day-labels {
  display: flex;
}

.day-label {
  width: calc(100% / 7.01);
}

.lines {
  position: absolute;
  width: calc(100% + 10px);
  height: 100%;
  top: 0;
  left: -10px;
}

.time-numbers {
  margin-top: 70px;
}

.day-name {
  margin-top: 10px;
  margin-bottom: -12px;
}
</style>

<style lang="scss">
.text-center {
  input {
    text-align: center;
  }
}
</style>
