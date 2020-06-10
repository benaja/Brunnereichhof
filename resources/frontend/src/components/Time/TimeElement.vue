<template>
  <div
    ref="timeElement"
    v-touch:touchhold="startTouchMove"
    :class="['time-element', value.worktype.color, { moving }]"
    :style="{ height, top }"
    @mousedown="startMouseMove"
    @click="edit"
  >
    <div class="time-element-content">
      <p class="white--text ml-1">
        {{ formatTime(value.from) }} - {{ formatTime(value.to) }} ({{ difference.toFixed(2) }}h)
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TimeElement',
  props: {
    value: {
      type: Object,
      default: null
    },
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
  computed: {
    difference() {
      const from = this.getDate(this.value.from)
      const to = this.getDate(this.value.to)

      const timeDiff = Math.abs(from.getTime() - to.getTime())
      const hoursDiff = timeDiff / 1000 / 60 / 60

      return hoursDiff
    },
    pixelPerHour() {
      const hoursDiff = this.maxStartHour - this.minStartHour
      return this.$refs.timeElement.parentElement.clientHeight / hoursDiff
    }
  },
  watch: {
    value() {
      this.updateTimeElemenPosition()
      this.origialValue = { ...this.value }
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
      window.addEventListener('touchmove', this.move, { passive: false })
      window.addEventListener('touchend', this.endTouchMove)
      this.$emit('scrolling', true)
      this.moving = true
    },
    startMouseMove(event) {
      this.lastPosition = event.clientY
      window.addEventListener('mousemove', this.move)
      window.addEventListener('mouseup', this.endMouseMove)
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
    },
    move(event) {
      const positionY = event.touches ? event.touches[0].clientY : event.clientY
      const hoursToAdd = (positionY - this.lastPosition) / this.pixelPerHour
      if (hoursToAdd >= 0.075 || hoursToAdd <= -0.075) {
        this.moving = true
        this.hasDragged = true
        let minutes = (this.startHour + hoursToAdd) * 60
        // make shure that you cant move the time element below 00:00
        if (minutes < 0) {
          minutes = 0
        }
        // make shure that you cant move the time element above 24:00
        if (minutes / 60 + this.difference > 24) {
          minutes = (24 - this.difference) * 60
        }
        this.startHour = (Math.round(minutes / 5) * 5) / 60
        this.lastPosition = positionY
        // make a copy of difference
        const { difference } = this
        this.value.from = this.getTimeString(this.startHour)
        this.value.to = this.getTimeString(this.startHour + difference)
        this.updateTimeElemenPosition()
      }
    },
    save() {
      if (this.hasDragged) {
        this.axios
          .put(`/times/${this.value.id}${this.urlWorkerParam}`, {
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
      const date = new Date()
      const timeSplits = timeString.split(':')
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
      const from = this.getDate(this.value.from)
      let hours = from.getHours()
      const minutes = from.getMinutes()
      hours += minutes / 60
      this.startHour = hours
    },
    getTimeString(time) {
      let hours = Math.floor(time)
      let minutes
      minutes = Math.round((time - Math.floor(time)) * 60)
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
      this.top = `${this.getPixelsFromTop()}px`
      this.height = `${this.pixelPerHour * this.difference}px`
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
