<template>
  <div class="white background" ref="background">
    <v-row wrap class="ma-0">
      <v-col class="side-bar">
        <range-picker v-model="dates"></range-picker>
        <div class="px-2">
          <v-select
            v-model="calendarSortType"
            :items="calendarSortTypes"
            color="blue"
            item-color="blue"
            prepend-inner-icon="sort"
            label="Sortieren nach"
          ></v-select>
        </div>
        <div class="px-2">
          <create-pdf></create-pdf>
          <stats ref="stats"></stats>
        </div>
      </v-col>
      <v-col class="content pa-0">
        <div class="callendar-container" ref="callendarContainer">
          <div
            class="day"
            v-for="(day, index) in days"
            :key="day.format('YYYY-MM-DD')"
            :style="{ width: `${100 / amountOfDays}%` }"
            @click="e => openReservationPopup(e, index)"
          >
            <div class="day-border"></div>
            <div class="day-content pt-2">
              <p class="text-center mb-0 overline">{{day.format('ddd')}}</p>
              <p class="text-center font-weight-bold">
                <span
                  v-if="day.format('D') == 1 || index === 0"
                >{{day.format('D')}}. {{day.format('MMM')}}</span>
                <span v-else>{{day.format('D')}}</span>
              </p>
              <!-- <v-divider></v-divider> -->
            </div>
          </div>
          <div class="reservations-container">
            <div
              v-for="(tag, index) of reservationTags"
              :key="'r' + index"
              :class="['reservation', 'blue', 'reservation-' + tag.reservation.id, ...tag.cssClass]"
              :style="tag.style"
              @click="e => openDetailsPopup(e, tag.reservation)"
              @mouseover="hover(tag.reservation.id)"
              @mouseleave="leave(tag.reservation.id)"
            >
              <p
                class="white--text ml-2 mr-1 caption"
              >{{ tag.reservation.employee.lastname }} {{ tag.reservation.employee.firstname }} | {{tag.reservation.bed_room_pivot.room.name}} / {{tag.reservation.bed_room_pivot.room.number}}</p>
            </div>
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
      max-width="100%"
      min-width="300"
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
      dates: [
        moment()
          .subtract(2, 'weeks')
          .startOf('day'),
        moment().startOf('day')
      ],
      dayHeight: 0,
      firstday: moment(),
      reservationTags: [],
      moreElementsTags: [],
      initialLoad: false,
      calendarSortType: localStorage.getItem('calendarSortType') || 'lastname',
      calendarSortTypes: [
        { text: 'Nachname', value: 'lastname' },
        { text: 'Zimmernummer', value: 'number' }
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
        this.reservationModel.y = event.clientY
        let selectedDate = this.dates[0].clone().add(day, 'days')
        setTimeout(() => {
          this.reservationModel.open = true
          this.$nextTick(() => {
            this.reservation.entry = selectedDate.format('YYYY-MM-DD')
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
        x += calendarContainerRect.width / this.amountOfDays
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
    getReservationsForSelectedTime() {
      return this.reservations.filter(
        r =>
          this.$moment(r.entry, 'YYYY-MM-DD').isSameOrBefore(this.dates[1], 'day') &&
          this.$moment(r.exit, 'YYYY-MM-DD').isSameOrAfter(this.dates[0], 'day')
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
      if (this.calendarSortType === 'number') {
        reservations.sort((a, b) => a.bed_room_pivot.room.number - b.bed_room_pivot.room.number)
      } else if (this.calendarSortType === 'lastname') {
        reservations.sort((a, b) => a.employee.lastname.toLowerCase().localeCompare(b.employee.lastname.toLowerCase()))
      }

      this.reservationTags = []
      let top = 0
      for (let reservation of reservations) {
        let marginLeft = 0
        let diffFromFirstDay = 0
        const isReservationEntrySameOrAfterTimeSelected = this.$moment(reservation.entry).isSameOrAfter(this.dates[0], 'day')
        const cssClass = []
        if (isReservationEntrySameOrAfterTimeSelected) {
          diffFromFirstDay = this.$moment(reservation.entry).diff(this.dates[0], 'days')
          marginLeft = `calc(${(100 / this.amountOfDays) * diffFromFirstDay}% + 5px)`
          cssClass.push('border-radius-left')
        }

        let width = '100%'
        let diffFromLastDay = this.dates[1].diff(this.$moment(reservation.exit), 'days')
        if (this.$moment(reservation.exit).isSameOrBefore(this.dates[1], 'day')) {
          // const diffFromLastDay = this.dates[1].diff(this.$moment(reservation.exit), 'days')
          const pixelsToAdd = isReservationEntrySameOrAfterTimeSelected ? 10 : 5
          width = `calc(${(100 / this.amountOfDays) * (this.amountOfDays - diffFromLastDay - diffFromFirstDay)}% - ${pixelsToAdd}px)`
          cssClass.push('border-radius-right')
        }

        if (this.amountOfDays - diffFromLastDay - diffFromFirstDay === 20) {
          console.log('width:', width)
          // console.log('')
        }

        let tag = {
          style: {
            marginLeft,
            width,
            top: `${top}px`
          },
          cssClass,
          reservation
        }
        this.reservationTags.push(tag)

        top += 25
      }
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
  min-height: calc(100vh - 64px);
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

.reservations-container {
  position: absolute;
  top: 80px;
  width: 100%;
  z-index: 2;
  max-height: calc(100vh - 145px);
  max-height: calc(var(--vh, 1vh) * 100 - 145px);
  overflow-y: scroll;
  overflow-x: hidden;

  &::-webkit-scrollbar {
    width: 0;
  }
}

.reservation {
  height: 20px;
  z-index: 2;
  // border-radius: 5px;
  margin-top: 5px;
  cursor: pointer;

  &.border-radius-left {
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
  }

  &.border-radius-right {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
  }

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

@media only screen and (max-width: 960px) {
  .side-bar,
  .content {
    width: 100%;
    flex: 0 0 100%;
    overflow-x: auto;
  }

  .day {
    min-width: 50px;
  }

  .callendar-container {
    position: relative;
    flex-wrap: nowrap;
    width: unset;
    display: inline-flex;
    min-width: 100%;
  }
}
</style>
