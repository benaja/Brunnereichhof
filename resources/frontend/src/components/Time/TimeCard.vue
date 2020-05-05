<template>
  <div
    v-if="open"
    class="background"
    @click="open = false"
  >
    <v-menu
      v-model="open"
      :close-on-content-click="false"
      :close-on-click="false"
      offset-y
      :position-x="positionX"
      :position-y="positionY"
      z-index="101"
      nudge-width="400"
      max-width="400"
    >
      <v-card>
        <p class="text-right upper-icon-bar">
          <v-btn
            v-if="timerecord"
            icon
            class="ma-1"
            @click="$refs.form.deleteTimerecord()"
          >
            <v-icon>delete</v-icon>
          </v-btn>
          <v-btn
            icon
            class="ma-1"
            @click="open = false"
          >
            <v-icon>close</v-icon>
          </v-btn>
        </p>
        <div class="px-3">
          <timerecord-form
            v-if="open"
            ref="form"
            :start-time="startTime"
            :date="day.date"
            :url-worker-param="urlWorkerParam"
            :timerecord="timerecord"
            @updated="updated"
            @isLoading="isLoading = $event"
          ></timerecord-form>
        </div>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            depressed
            :loading="isLoading"
            @click="$refs.form.save()"
          >
            {{ timerecord ? 'Aktualisieren' : 'Speichern' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-menu>
  </div>
</template>

<script>
import TimerecordForm from '@/components/Time/TimerecordForm'

export default {
  components: {
    TimerecordForm
  },
  props: {
    urlWorkerParam: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      positionX: 0,
      positionY: 0,
      open: false,
      startTime: null,
      day: {},
      timerecord: {},
      isLoading: false
    }
  },
  methods: {
    openNewTimerecord({
      day, index, selectedStartHour, event, pixelPerHour, timerecord
    }) {
      if (timerecord) {
        const startDate = this.$moment(timerecord.from, 'HH:mm')
        const startHour = startDate.hour() + startDate.minutes() / 60
        const scrollContainer = document.getElementsByClassName('scroll-container')[0]
        const scrollRect = scrollContainer.getBoundingClientRect()
        this.positionY = pixelPerHour * startHour - scrollContainer.scrollTop + scrollRect.y
      } else {
        this.positionY = event.clientY
      }
      const daysContainer = document.getElementsByClassName('days')[0]
      const timeElementWidth = document.getElementsByClassName('time-elements')[0].offsetWidth
      const rect = daysContainer.getBoundingClientRect()
      const relative = daysContainer.offsetWidth - timeElementWidth * (7 - index)
      let positionX = rect.left + relative - 400
      if (positionX < 0) {
        positionX += 400 + timeElementWidth
      }
      this.positionX = positionX
      this.startTime = selectedStartHour
      this.day = day
      this.timerecord = timerecord
      this.open = false
      setTimeout(() => {
        this.open = true
      }, 100)
    },
    updated(newTimerecords) {
      this.day.hours = newTimerecords
      this.$emit('updated', this.day)
      this.open = false
    }
  }
}
</script>

<style lang="scss" scoped>
.background {
  position: fixed;
  width: 100vw;
  height: 100vh;
  top: 0;
  left: 0;
  z-index: 20;
  background-color: rgba(0, 0, 0, 0.3);
}

.upper-icon-bar {
  margin-bottom: 0;
}
</style>
