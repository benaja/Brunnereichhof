<template>
  <div>
    <v-row wrap v-if="$store.getters.isMobile">
      <v-col cols="12">
        <p class="text-center mt-2 font-weight-bold day-name">{{dayName}}</p>
      </v-col>
      <v-col cols="2">
        <v-btn text icon @click="previousDay">
          <v-icon>keyboard_arrow_left</v-icon>
        </v-btn>
      </v-col>
      <v-col cols="8" class="text-center">
        <v-dialog v-model="dateDialog" width="290px" class="text-center">
          <template v-slot:activator="{ on }">
            <v-text-field v-on="on" v-model="date" readonly class="text-center"></v-text-field>
          </template>
          <v-date-picker
            v-model="date"
            scrollable
            first-day-of-week="1"
            locale="ch-de"
            @change="dateDialog = false"
          ></v-date-picker>
        </v-dialog>
      </v-col>
      <v-col cols="2">
        <v-btn text icon @click="nextDay">
          <v-icon>keyboard_arrow_right</v-icon>
        </v-btn>
      </v-col>
      <v-col cols="2">
        <div v-for="index in numbers" :key="index" class="time">
          <p class="text-right pr-3">{{index * 2 - 2}}:00</p>
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
          ></day>
        </v-touch>
      </v-col>
      <overview v-if="isOverviewOpen" @close="isOverviewOpen = false"></overview>
      <time-popup
        v-model="timePopupForm.isOpen"
        :date="timePopupForm.date"
        :settings="settings"
        :timerecord="timePopupForm.timerecord"
        @add="newTimerecords => { timePopupForm.day.hours = newTimerecords }"
      />
    </v-row>
    <v-row v-else wrap>
      <v-col cols="3" xl="2" class="pb-0">
        <div class="overview-container">
          <overview @change="newDate => date = newDate" ref="overview"></overview>
          <time-popup
            v-model="timePopupForm.isOpen"
            :date="timePopupForm.date"
            :settings="settings"
            :timerecord="timePopupForm.timerecord"
            @add="newTimerecords => { timePopupForm.day.hours = newTimerecords; $refs.overview.getStats() }"
          />
        </div>
      </v-col>
      <v-col cols="1" xl="1" class="time-numbers">
        <div v-for="index in numbers" :key="index" class="time">
          <p class="text-right pr-3">{{index * 2 - 2}}:00</p>
        </div>
      </v-col>
      <v-col cols="8" xl="9" class="pr-2">
        <div class="days">
          <div v-for="(day, index) of weekDays" :key="index">
            <day
              :date="(new Date(day.date)).toISOString().substr(0, 10)"
              v-model="day.hours"
              :settings="settings"
              @update="$refs.overview.getStats()"
              @add="date => openTimePopup({}, day)"
              @edit="timerecord => openTimePopup(timerecord, day)"
            ></day>
          </div>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import Day from '@/components/Time/Day'
import Overview from '@/components/Time/Overview'
import TimePopup from '@/components/Time/TimePopup'

export default {
  name: 'Time',
  components: {
    Day,
    Overview,
    TimePopup
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
      .get(process.env.VUE_APP_API_URL + 'settings/time')
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
        url = process.env.VUE_APP_API_URL + 'time/' + this.date
      } else {
        url = process.env.VUE_APP_API_URL + 'time/week/' + this.date
      }
      this.axios
        .get(url)
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
    }
  },
  computed: {
    dayName() {
      let date = new Date(this.date)
      // set sunday as last day of month
      let day = date.getDay() === 0 ? 7 : date.getDay()
      day--
      return this.$store.getters.dayNames[day]
    }
  },
  watch: {
    date() {
      this.getDay()
    }
  }
}
</script>

<style lang="scss">
.text-center {
  input {
    text-align: center;
  }
}

.time {
  height: 9vh;

  p {
    margin-top: -1vh;
  }
}

.overview-container {
  position: relative;
  width: 100%;
  height: calc(100vh - 64px);
}

.days {
  display: flex;
  flex-wrap: wrap;
  margin-top: 70px;

  > div {
    width: calc(100% / 7.01);
  }
}

.time-numbers {
  margin-top: 70px;
}

.day-name {
  margin-top: 10px;
  margin-bottom: -12px;
}
</style>
