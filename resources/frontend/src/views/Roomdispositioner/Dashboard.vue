<template>
  <div class="white background" ref="background">
    <v-row>
      <v-col class="side-bar">
        <range-picker v-model="dates"></range-picker>
        <!-- <v-date-picker
          class="elevation-0"
          v-model="date"
          no-title
          locale="ch-de"
          first-day-of-week="1"
          color="blue"
          range
          show-week
          :day-format="dayFormat"
        ></v-date-picker>-->
        <div class="px-2">
          <v-select
            v-model="calendarType"
            :items="calendarTypeOptions"
            outline
            color="blue"
            single-line
            prepend-inner-icon="calendar_view_day"
          ></v-select>
          <v-select
            v-model="calendarSortType"
            :items="calendarSortTypes"
            outline
            color="blue"
            single-line
            prepend-inner-icon="sort"
          ></v-select>
        </div>
        <div class="px-2">
          <create-pdf></create-pdf>
          <stats ref="stats"></stats>
        </div>
      </v-col>
      <v-col class="content">
        <div class="week-days">
          <div
            v-for="index in 7"
            :key="'d-' + index"
            class="week-day text-center"
          >{{firstday.clone().add(index - 1, 'days').format('ddd')}}</div>
        </div>
        <div class="callendar-container" ref="callendarContainer">
          <div
            class="day"
            v-for="(day, index) in days"
            :key="day.format('YYYY-MM-DD')"
            :style="{ height: '500px', width: `calc(100% / ${amountOfDays})` }"
            @click="e => openReservationPopup(e, index)"
          >
            <div class="day-border"></div>
            <div class="day-content pt-2">
              <!-- <p class="text-center mb-2" v-if="index < 7">{{day.date.format('ddd')}}</p> -->
              <p :class="['text-center', { 'font-weight-bold': isCurrentMonth(index + 1) }]">
                {{day.format('D')}}
                <span v-if="day.format('D') == 1">. {{day.format('MMM')}}</span>
              </p>
            </div>
          </div>
          <div
            v-for="(tag, index) of reservationTags"
            :key="'r' + index"
            :class="['reservation', 'blue', 'reservation-' + tag.reservation.id]"
            :style="tag.style"
            @click="e => openDetailsPopup(e, tag.reservation)"
            @mouseover="hover(tag.reservation.id)"
            @mouseleave="leave(tag.reservation.id)"
          >
            <p
              class="white--text ml-2 mr-1 caption"
            >{{ tag.reservation.employee.lastname }} {{ tag.reservation.employee.firstname }} | {{tag.reservation.bed_room_pivot.room.name}} / {{tag.reservation.bed_room_pivot.room.number}}</p>
          </div>
          <div
            v-for="(tag, index) of moreElementsTags"
            :key="'m' + index"
            class="more-elements pa-1"
            :style="tag.style"
          >
            <p
              class="text-center ma-0"
              @click="e => openMoreElementsPopup(e, tag)"
            >{{ tag.amountOfMoreElements }} {{ tag.amountOfMoreElements === 1 ? 'weitere Reservation' : 'weitere Reservationen' }}</p>
          </div>
        </div>
      </v-col>
    </v-row>
    <v-menu
      :close-on-content-click="false"
      :nudge-width="300"
      v-model="reservationModel.open"
      obsolute
      :position-x="reservationModel.x"
      :position-y="reservationModel.y"
      z-index="3"
    >
      <create-reservation
        v-model="reservationModel.open"
        :reservation="reservation"
        @add="reservation => reservations.push(reservation)"
        @updateAll="newReservations => reservations = newReservations"
      ></create-reservation>
    </v-menu>
    <v-menu
      :close-on-content-click="false"
      offset-x
      v-model="moreElementsModel.open"
      absolute
      :position-x="moreElementsModel.x"
      :position-y="moreElementsModel.y"
      z-index="101"
      :nudge-width="moreElementsModel.width"
      max-width="300"
      close-delay="10"
    >
      <v-card class="pa-3 more-elements-popup">
        <p class="text-center">{{ moreElementsModel.date.format('ddd') }}</p>
        <h2 class="text-center">{{ moreElementsModel.date.format('D') }}</h2>
        <div
          class="reservation blue my-2"
          v-for="(reservation, index) of moreElementsModel.reservations"
          :key="'rm' + index"
          @click="e => openDetailsPopup(e, reservation, moreElementsModel.date)"
        >
          <p
            class="white--text ml-2 mr-2"
          >{{ reservation.employee.lastname }} {{ reservation.employee.firstname }} | {{reservation.bed_room_pivot.room.name}} / {{reservation.bed_room_pivot.room.number}}</p>
        </div>
      </v-card>
    </v-menu>
    <v-menu
      :close-on-content-click="false"
      v-model="detailsModel.open"
      :position-x="detailsModel.x"
      :position-y="detailsModel.y"
      z-index="4"
      max-width="400"
      min-width="400"
      close-delay="10"
    >
      <reservation-details
        v-model="detailsModel.reservation"
        :selectedDay="detailsModel.clickedDay"
        @close="detailsModel.open = false"
        @update="drawReservations"
        @delete="deleteReservation"
        @updateAll="newReservations => reservations = newReservations"
      />
    </v-menu>
  </div>
</template>

<script>
import moment from 'moment'
import Stats from '@/components/Roomdispositioner/Stats'
import CreatePdf from '@/components/Roomdispositioner/CreatePdf'
import CreateReservation from '@/components/Roomdispositioner/CreateReservation'
import ReservationDetails from '@/components/Roomdispositioner/ReservationDetails'
import RangePicker from '@/components/Roomdispositioner/RangePicker'

export default {
  name: 'Dashboard',
  components: {
    Stats,
    CreatePdf,
    CreateReservation,
    ReservationDetails,
    RangePicker
  },
  data() {
    return {
      reservationModel: {
        open: false,
        x: 0,
        y: 0
      },
      moreElementsModel: {
        open: false,
        x: 0,
        y: 0,
        width: 200,
        date: moment(),
        reservations: []
      },
      detailsModel: {
        open: false,
        x: 0,
        y: 0,
        reservation: {},
        clickedDay: moment()
      },
      reservation: {
        entry: null
      },
      reservations: [],
      dates: [moment(new Date()).subtract(2, 'weeks'), moment(new Date())],
      dayHeight: 0,
      firstday: moment(),
      reservationTags: [],
      moreElementsTags: [],
      initialLoad: false,
      calendarTypeOptions: [
        { text: 'Monat', value: 'month' },
        { text: 'Woche', value: 'week' }
      ],
      calendarType: localStorage.getItem('calendarType') || 'month',
      calendarSortType: localStorage.getItem('calendarSortType') || 'lastname',
      calendarSortTypes: [
        { text: 'Nachname', value: 'lastname' },
        { text: 'Zimmernummer', value: 'number' },
        { text: 'Auffüllen', value: 'fill' }
      ]
    }
  },
  mounted() {
    this.dayHeight = this.getDayHeight
    this.firstday = this.firstDate
    this.axios.get(`/reservations?entry=${this.firstDate.format('YYYY-MM-DD')}&exit="${this.lastDate.format('YYYY-MM-DD')}`).then(response => {
      this.reservations = response.data
    })
  },
  methods: {
    isCurrentMonth(day) {
      return (
        moment(this.date).format('MM') ===
        this.firstday
          .clone()
          .add(day - 1, 'days')
          .format('MM')
      )
    },
    openReservationPopup(e, day) {
      if (!this.moreElementsModel.open && !this.detailsModel.open && this.$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])) {
        e.preventDefault()
        let targetElement = e.target
        while (!targetElement.classList.contains('day')) {
          targetElement = targetElement.parentNode
        }
        let position = targetElement.getBoundingClientRect()

        this.reservationModel.open = false
        this.reservationModel.x = position.x + position.width
        this.reservationModel.y = position.y
        setTimeout(() => {
          this.reservationModel.open = true
          this.$nextTick(() => {
            this.reservation.entry = this.firstday
              .clone()
              .add(day, 'days')
              .format('YYYY-MM-DD')
          })
        }, 100)
      }
    },
    openMoreElementsPopup(e, tag) {
      e.preventDefault()
      this.moreElementsModel.open = false
      let position = this.$refs.callendarContainer.getBoundingClientRect()
      this.moreElementsModel.x = position.left + (position.width / 7) * tag.day - 20
      this.moreElementsModel.y = this.dayHeight * tag.week + 64 - 20
      this.moreElementsModel.width = position.width / 7 + 40
      this.moreElementsModel.date = moment(
        this.firstday
          .clone()
          .add(tag.day, 'days')
          .add(tag.week, 'weeks')
      )
      if (this.calendarSortType === 'number') {
        this.moreElementsModel.reservations = tag.reservations.sort((a, b) => a.bed_room_pivot.room.number - b.bed_room_pivot.room.number)
      } else {
        this.moreElementsModel.reservations = tag.reservations.sort((a, b) => {
          return a.employee.lastname.toLowerCase().localeCompare(b.employee.lastname.toLowerCase())
        })
      }

      setTimeout(() => {
        this.moreElementsModel.open = true
      }, 100)
    },
    openDetailsPopup(e, reservation, date = null) {
      e.preventDefault()
      this.detailsModel.open = false
      let position = e.target.getBoundingClientRect()

      this.detailsModel.y = position.top
      let calendarContainerRect = this.$refs.callendarContainer.getBoundingClientRect()
      let x = calendarContainerRect.left
      while (x < e.clientX) {
        x += calendarContainerRect.width / 7
      }
      this.detailsModel.x = x
      reservation.employee.name = `${reservation.employee.lastname} ${reservation.employee.firstname}`
      this.detailsModel.reservation = reservation

      if (date) {
        this.detailsModel.clickedDay = date
      } else {
        let day = Math.floor((e.x - calendarContainerRect.x) / (calendarContainerRect.width / 7))
        let week = Math.floor((e.y - calendarContainerRect.y) / (calendarContainerRect.height / (this.amountOfDays / 7)))
        this.detailsModel.clickedDay = moment(this.firstDate)
          .add(week, 'weeks')
          .add(day, 'days')
      }

      setTimeout(() => {
        this.detailsModel.open = true
      }, 100)
    },
    reservationsThisDay(day) {
      return this.reservations.filter(r => {
        let currentDay = this.firstday.clone().add(day - 1, 'days')
        return moment(r.entry).isSameOrBefore(currentDay, 'day') && moment(r.exit).isSameOrAfter(currentDay, 'day')
      })
    },
    getReservationsForSelectedTime(week) {
      return this.reservations.filter(
        r =>
          this.$moment(r.entry, 'YYYY-MM-DD', 'de-ch').isSameOrBefore(this.dates[1], 'week') &&
          this.$moment(r.exit, 'YYYY-MM-DD', 'de-ch').isSameOrAfter(this.dates[0], 'week')
      )
    },
    isFirstDay(day, reservation) {
      if ((day - 1) % 7 === 0) return true
      if (
        this.firstday
          .clone()
          .add(day - 1, 'days')
          .isSame(reservation.entry, 'day')
      ) {
        return true
      }
      return false
    },
    drawReservations() {
      if (this.initialLoad) {
        this.$refs.stats.getStats()
      } else {
        this.initialLoad = true
      }
      let reservations = this.getReservationsForSelectedTime()
      console.log(reservations)
      // this.detailsModel.open = false
      // this.reservationTags = []
      // this.moreElementsTags = []
      // for (let week = 0; week < this.amountOfDays / 7; week++) {
      //   let reservationsPerWeekDay = []
      //   let reservations = this.reservationsThisWeek(week)

      //   let monday = this.firstday.clone().add(week, 'weeks')
      //   let sunday = monday.clone().endOf('isoweek')
      //   for (let reservation of reservations) {
      //     this.getFirstDayFromReservationAndWeek(reservation, week, monday)
      //     this.getLastDayFromReservationAndWeek(reservation, week, sunday)
      //   }

      //   if (this.calendarSortType === 'number') {
      //     reservations.sort((a, b) => a.bed_room_pivot.room.number - b.bed_room_pivot.room.number)
      //   } else if (this.calendarSortType === 'lastname') {
      //     reservations.sort((a, b) => a.employee.lastname.toLowerCase().localeCompare(b.employee.lastname.toLowerCase()))
      //   } else {
      //     reservations.sort((reservation1, reservation2) => {
      //       let duration1 = reservation1.lastDayOfWeek.diff(reservation1.firstDayOfWeek, 'days')
      //       let duration2 = reservation2.lastDayOfWeek.diff(reservation2.firstDayOfWeek, 'days')

      //       if (duration1 > duration2) return -1
      //       if (duration1 < duration2) return 1
      //       return 0
      //     })
      //   }

      //   let count = 0
      //   let hasMoreElementsTags = 0
      //   for (let reservation of reservations) {
      //     let diffMonday = reservation.firstDayOfWeek.diff(monday, 'days')
      //     let diffFromTo = reservation.lastDayOfWeek.diff(reservation.firstDayOfWeek, 'days')
      //     let reservedDaysCurrentWeek = [0, 0, 0, 0, 0, 0, 0]
      //     if (this.calendarSortType === 'lastname' || this.calendarSortType === 'number') {
      //       reservedDaysCurrentWeek = [1, 1, 1, 1, 1, 1, 1]
      //     }
      //     for (let i = diffMonday; i < diffMonday + diffFromTo + 1; i++) {
      //       reservedDaysCurrentWeek[i] = 1
      //     }

      //     let index = count
      //     if (this.calendarType === 'month') {
      //       let freePlace = reservationsPerWeekDay.find(r => {
      //         let isCompatible = true
      //         for (let i = 0; i < r.length; i++) {
      //           if (reservedDaysCurrentWeek[i] === 1 && r[i] === 1) {
      //             isCompatible = false
      //           }
      //         }
      //         return isCompatible
      //       })
      //       if (freePlace) {
      //         index = reservationsPerWeekDay.indexOf(freePlace)
      //         for (let i = 0; i < freePlace.length; i++) {
      //           if (reservedDaysCurrentWeek[i] === 1) {
      //             freePlace[i] = 1
      //           }
      //         }
      //       } else {
      //         index = reservationsPerWeekDay.length
      //         reservationsPerWeekDay.push(reservedDaysCurrentWeek)
      //       }
      //     }

      //     let top = 25 * index + 40
      //     if (this.calendarType !== 'month' && top >= this.dayHeight - 40) {
      //       this.dayHeight += 25
      //     }
      //     if (top < this.dayHeight - 40) {
      //       let tag = {
      //         style: {
      //           top: top + this.getDayHeight * week + 'px',
      //           left: 'calc(' + (100 / 7) * diffMonday + '% + 5px)',
      //           width: 'calc(' + (100 / 7) * (diffFromTo + 1) + '% - 10px)'
      //         },
      //         reservation: reservation
      //       }
      //       this.reservationTags.push(tag)
      //     } else {
      //       hasMoreElementsTags++
      //     }
      //     count++
      //   }
      //   if (hasMoreElementsTags > 0) {
      //     let maxElementsOnOneDay = Math.floor((this.dayHeight - 80) / 30) + 1
      //     for (let day = 0; day < 7; day++) {
      //       let amountOfMoreElements = 0
      //       for (let i = maxElementsOnOneDay; i < reservationsPerWeekDay.length; i++) {
      //         if (reservationsPerWeekDay[i][day] === 1) {
      //           amountOfMoreElements++
      //         }
      //       }
      //       if (amountOfMoreElements > 0) {
      //         let reservationsThisDay = this.reservationsThisDay(7 * week + day + 1)
      //         this.moreElementsTags.push({
      //           style: {
      //             top: this.dayHeight * week + this.dayHeight - 30 + 'px',
      //             left: (100 / 7) * day + '%'
      //           },
      //           reservations: reservationsThisDay,
      //           amountOfMoreElements: amountOfMoreElements,
      //           day,
      //           week
      //         })
      //       }
      //     }
      //   }
      // }
    },
    getFirstDayFromReservationAndWeek(reservation, week, monday) {
      if (moment(reservation.entry).diff(monday, 'days') < 0) {
        reservation.firstDayOfWeek = monday
        return monday
      } else {
        reservation.firstDayOfWeek = moment(reservation.entry, 'YYYY-MM-DD', 'de-ch')
        return reservation.firstDayOfWeek
      }
    },
    getLastDayFromReservationAndWeek(reservation, week, sunday) {
      if (moment(reservation.exit).diff(sunday, 'days') >= 0) {
        reservation.lastDayOfWeek = sunday
        return sunday
      } else {
        reservation.lastDayOfWeek = moment(reservation.exit, 'YYYY-MM-DD', 'de-ch')
        return reservation.lastDayOfWeek
      }
    },
    deleteReservation(reservation) {
      this.reservations.splice(this.reservations.indexOf(reservation), 1)
      this.reservations = [...this.reservations]
      this.drawReservations()
    },
    hover(reservationId) {
      let reservations = document.getElementsByClassName('reservation-' + reservationId)
      for (let reservation of reservations) {
        reservation.style.filter = 'brightness(85%)'
      }
    },
    leave(reservationId) {
      let reservations = document.getElementsByClassName('reservation-' + reservationId)
      for (let reservation of reservations) {
        reservation.style.filter = 'unset'
      }
    }
  },
  computed: {
    firstDate() {
      if (this.calendarType === 'month') {
        return moment(this.date, 'YYYY-MM-DD', 'de-ch')
          .startOf('month')
          .startOf('isoWeek')
      } else {
        return moment(this.date, 'YYYY-MM-DD', 'de-ch').startOf('isoWeek')
      }
    },
    lastDate() {
      if (this.calendarType === 'month') {
        return moment(this.date, 'YYYY-MM-DD', 'de-ch')
          .endOf('month')
          .endOf('isoWeek')
      } else {
        return moment(this.date, 'YYYY-MM-DD', 'de-ch').endOf('isoWeek')
      }
    },
    days() {
      let days = []
      let day = this.dates[0].clone()
      for (let i = 0; i < this.amountOfDays; i++) {
        let currentDay = day.clone().add(i, 'days')
        days.push(currentDay)
      }
      return days
    },
    amountOfDays() {
      return this.dates[1].diff(this.dates[0], 'days') + 1
    },
    getDayHeight() {
      if (this.calendarType === 'month') {
        let weeks = this.amountOfDays / 7
        return (this.$refs.background.clientHeight - 20) / weeks
      } else {
        return this.$refs.background.clientHeight - 20
      }
    }
  },
  watch: {
    dates() {
      this.drawReservations()
    },
    reservations() {
      this.drawReservations()
    },
    calendarType() {
      localStorage.setItem('calendarType', this.calendarType)
      this.dayHeight = this.getDayHeight
      this.firstday = this.firstDate
      this.drawReservations()
    },
    calendarSortType() {
      localStorage.setItem('calendarSortType', this.calendarSortType)
      this.drawReservations()
    }
  }
}
</script>

<style lang="scss" scoped>
.side-bar {
  width: 300px;
  flex: 0 0 300px;
}

.content {
  width: calc(100% - 300px);
  flex: 0 0 calc(100% - 300px);
}

.background {
  min-height: calc(100vh - 64px);
}

.callendar-container {
  position: relative;
  display: flex;
  flex-wrap: wrap;
}

.week-days {
  display: flex;
  flex-wrap: wrap;
}

.week-day {
  width: calc(100% / 7.001);
  border-left: 1px solid lightgray;
  height: 20px;
  line-height: 30px;
}

.day {
  // width: calc(100% / 7.001);
  position: relative;
}

.day-border {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  border-left: 1px solid lightgray;
  border-bottom: 1px solid lightgray;
  z-index: 1;
}

.day-content {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  z-index: 2;
}

.reservation {
  height: 20px;
  position: absolute;
  z-index: 2;
  border-radius: 5px;
  cursor: pointer;

  p {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
  }

  &:hover {
    filter: brightness(85%);
  }
}

.more-elements {
  position: absolute;
  z-index: 2;
  width: calc(100% / 7);

  p {
    cursor: pointer;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;

    &:hover {
      background-color: rgb(238, 238, 238);
      box-shadow: 0 0 10px lightgray;
    }
  }
}

.more-elements-popup {
  width: 100%;

  .reservation {
    position: relative;
  }
}
</style>
