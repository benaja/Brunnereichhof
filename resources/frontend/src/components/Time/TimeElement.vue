<template>
  <drag-it-dude
    :class="['time-element', value.worktype.color]"
    :style="{ height: difference * 4 + 'vh', top }"
    ref="timeElement"
    @dropped="save"
    @dragging="onDrag"
  >
    <div class="time-element-content" @mouseup="edit">
      <p class="white--text ml-1">{{ formatTime(value.from) }} - {{ formatTime(value.to) }} ({{ difference.toFixed(2) }}h)</p>
    </div>
    <!-- <div
    :class="['time-element', value.worktype.color]"
    :style="{height: difference * 4 + 'vh', top: startHour * 4 - 16 + 'vh'}"
    @click="$emit('edit')"
    ref="timeElement"
    >-->
  </drag-it-dude>
</template>

<script>
import DragItDude from 'vue-drag-it-dude'

export default {
  name: 'TimeElement',
  components: {
    DragItDude
  },
  props: {
    value: Object,
    urlWorkerParam: String
  },
  data() {
    return {
      lastPosition: 0,
      startHour: 0,
      top: 0,
      hasDragged: false
    }
  },
  mounted() {
    this.setStartHour()
    this.top = this.getPixelsFromTop() + 'px'
  },
  methods: {
    onDrag() {
      let minStartHour = 4
      let maxStartHour = 20
      let hoursDiff = maxStartHour - minStartHour
      let hoursPerPixel = hoursDiff / this.$refs.timeElement.elem.parentElement.clientHeight
      this.startHour = minStartHour + hoursPerPixel * this.$refs.timeElement.top
      let difference = this.difference
      this.value.from = this.getTimeString(this.startHour)
      this.value.to = this.getTimeString(this.startHour + difference)
      this.hasDragged = true
    },
    save() {
      if (this.hasDragged) {
        this.axios
          .put(`/time/${this.value.id}${this.urlWorkerParam}`, {
            ...this.value,
            worktype: this.value.worktype.id
          })
          .then(() => {
            this.$store.dispatch('alert', { text: 'Erfolgreich gespeichert' })
          })
          .catch(() => {
            this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
          })
          .finally(() => {
            this.hasDragged = false
          })
      }
    },
    edit() {
      if (!this.hasDragged) {
        this.$emit('edit')
      }
    },
    getPixelsFromTop() {
      let minStartHour = 4
      let maxStartHour = 20
      let hoursDiff = maxStartHour - minStartHour
      let pixelPerHour = this.$refs.timeElement.elem.parentElement.clientHeight / hoursDiff
      return (this.startHour - minStartHour) * pixelPerHour
    },
    getDate(timeString) {
      let date = new Date()
      let timeSplits = timeString.split(':')
      date.setHours(timeSplits[0])
      date.setMinutes(timeSplits[1])
      return date
    },
    formatTime(time) {
      time = time.split(':')
      time = `${time[0]}:${time[1]}`
      return time
    },
    setStartHour() {
      let from = this.getDate(this.value.from)
      let hours = from.getHours()
      let minutes = from.getMinutes()
      hours += minutes / 60
      this.startHour = hours
    },
    start() {
      this.lastPosition = event.clientY
      this.$refs.timeElement.addEventListener('mousemove', this.move)
    },
    end() {
      this.$refs.timeElement.removeEventListener('mousemove', this.move)
    },
    move() {
      let hoursToAdd = (event.clientY - this.lastPosition) / (window.innerHeight / 25)
      if (hoursToAdd >= 0.075 || hoursToAdd <= -0.075) {
        this.startHour += hoursToAdd
        this.lastPosition = event.clientY
        let difference = this.difference
        this.value.from = this.getTimeString(this.startHour)
        this.value.to = this.getTimeString(this.startHour + difference)
      }
    },
    getTimeString(time) {
      let hours = Math.floor(time)
      let minutes = Math.round(((time - Math.floor(time)) * 60) / 5) * 5
      if (minutes === 60) {
        minutes = 0
        hours++
      }
      let formatedTimeFrom = hours >= 10 ? `${hours}:` : `0${hours}:`
      formatedTimeFrom += minutes >= 10 ? minutes : `0${minutes}`
      return formatedTimeFrom
    }
  },
  computed: {
    difference() {
      let from = this.getDate(this.value.from)
      let to = this.getDate(this.value.to)

      let timeDiff = Math.abs(from.getTime() - to.getTime())
      let hoursDiff = timeDiff / 1000 / 60 / 60

      return hoursDiff
    }
  },
  watch: {
    value() {
      this.setStartHour()
    }
  }
}
</script>

<style lang="scss" scoped>
.time-element {
  position: absolute;
  width: 100%;
  height: 5vh;
  top: 0;
  left: 0;
  cursor: pointer;
  min-height: 2vh;
  user-select: none;
  z-index: 2;
}

.time-element-content {
  height: 100%;
}
</style>
