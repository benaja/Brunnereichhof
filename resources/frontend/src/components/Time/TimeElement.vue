<template>
  <div
    :class="['time-element', value.worktype.color, { moving }]"
    :style="{ height, top }"
    ref="timeElement"
    v-touch:touchhold="startTouchMove"
    @mousedown="startMouseMove"
    @click="edit"
  >
    <div class="time-element-content">
      <p class="white--text ml-1">{{ formatTime(value.from) }} - {{ formatTime(value.to) }} ({{ difference.toFixed(2) }}h)</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TimeElement',
  props: {
    value: Object,
    urlWorkerParam: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      lastPosition: 0,
      startHour: 0,
      top: 0,
      height: 0,
      hasDragged: false,
      minStartHour: 0,
      maxStartHour: 24,
      origialValue: {},
      hide: false,
      mousedown: false,
      moving: false
    }
  },
  mounted() {
    this.updateTimeElemenPosition()
    this.origialValue = { ...this.value }
  },
  methods: {
    startTouchMove(event) {
      if (!event.touches) return
      this.lastPosition = event.touches[0].clientY
      window.addEventListener('touchmove', this.move)
      window.addEventListener('touchend', this.endTouchMove)
      this.$emit('scrolling', true)
      this.moving = true
    },
    startMouseMove(event) {
      this.lastPosition = event.clientY
      window.addEventListener('mousemove', this.move)
      window.addEventListener('mouseup', this.endMouseMove)
      this.$emit('moving', true)
    },
    endTouchMove() {
      window.removeEventListener('touchmove', this.move)
      window.removeEventListener('touchend', this.endTouchMove)
      this.$emit('scrolling', false)
      this.save()
      this.moving = false
    },
    endMouseMove(event) {
      event.preventDefault()
      window.removeEventListener('mousemove', this.move)
      window.removeEventListener('mouseup', this.endMouseMove)
      this.save()
      this.moving = false
      this.$emit('moving', false)
    },
    move(event) {
      const positionY = event.touches ? event.touches[0].clientY : event.clientY
      let hoursToAdd = (positionY - this.lastPosition) / this.pixelPerHour
      if (hoursToAdd >= 0.075 || hoursToAdd <= -0.075) {
        this.moving = true
        this.hasDragged = true
        this.startHour += hoursToAdd
        this.lastPosition = positionY
        let difference = this.difference
        this.value.from = this.getTimeString(this.startHour)
        this.setStartHour()
        this.value.to = this.getTimeString(this.startHour + difference, true)
        this.updateTimeElemenPosition()
      }
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
            this.origialValue = { ...this.value }
          })
          .catch(error => {
            if (error.includes('Die Zeit überschneidet sich mit einem anderen Eintrag.')) {
              this.value.from = this.origialValue.from
              this.value.to = this.origialValue.to
              this.updateTimeElemenPosition()
              this.hide = true
              this.$nextTick(() => {
                this.hide = false
              })
              this.$store.dispatch('alert', { text: 'Kollision mit einem anderen Eintrag', type: 'error' })
            } else {
              this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
            }
          })
          .finally(() => {
            this.hasDragged = false
          })
      }
    },
    edit() {
      if (!this.hasDragged) {
        this.$emit('edit')
        this.mousedown = false
      }
    },
    getPixelsFromTop() {
      return (this.startHour - this.minStartHour) * this.pixelPerHour
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
    getTimeString(time, dontRoundToFiveMinutes = false) {
      let hours = Math.floor(time)
      let minutes
      if (dontRoundToFiveMinutes) {
        minutes = Math.round((time - Math.floor(time)) * 60)
      } else {
        minutes = Math.round(((time - Math.floor(time)) * 60) / 5) * 5
      }
      if (minutes === 60) {
        minutes = 0
        hours++
      }
      let formatedTimeFrom = hours >= 10 ? `${hours}:` : `0${hours}:`
      formatedTimeFrom += minutes >= 10 ? minutes : `0${minutes}`
      return formatedTimeFrom
    },
    updateTimeElemenPosition() {
      this.setStartHour()
      this.top = this.getPixelsFromTop() + 'px'
      this.height = this.pixelPerHour * this.difference + 'px'
    }
  },
  computed: {
    difference() {
      let from = this.getDate(this.value.from)
      let to = this.getDate(this.value.to)

      let timeDiff = Math.abs(from.getTime() - to.getTime())
      let hoursDiff = timeDiff / 1000 / 60 / 60

      return hoursDiff
    },
    pixelPerHour() {
      let hoursDiff = this.maxStartHour - this.minStartHour
      return this.$refs.timeElement.parentElement.clientHeight / hoursDiff
    }
  },
  watch: {
    value() {
      this.updateTimeElemenPosition()
      this.origialValue = { ...this.value }
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

  &.moving {
    filter: brightness(105%);
    box-shadow: gray 0 0 20px;
  }
}

.time-element-content {
  height: 100%;
}
</style>
