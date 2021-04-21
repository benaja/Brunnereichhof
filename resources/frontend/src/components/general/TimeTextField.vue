<template>
  <v-text-field
    v-model="internalValue"
    v-bind="$attrs"
  ></v-text-field>
</template>

<script>
export default {
  props: {
    value: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      internalValue: this.value
    }
  },

  watch: {
    internalValue() {
      const oldTime = this.$moment(this.value, 'HH:mm')
      const newTime = this.$moment(this.internalValue, 'HH:mm')

      if (newTime.isValid() && !this.isSameTime(newTime, oldTime)) {
        this.$emit('input', newTime.format('HH:mm'))
      }
    },

    value() {
      const newTime = this.$moment(this.value, 'HH:mm')
      const time = this.$moment(this.internalValue, 'HH:mm')

      // only update internal time, when new time is different
      if (time.isValid() && !this.isSameTime(newTime, time)) {
        this.internalValue = time.format('HH:mm')
      }
    }
  },

  mounted() {
    if (this.value) {
      const time = this.$moment(this.value, 'HH:mm')
      if (time.isValid()) {
        this.internalValue = time.format('HH:mm')
      }
    }
  },

  methods: {
    isSameTime(time1, time2) {
      return time1.hours() === time2.hours()
        && time1.minutes() === time2.minutes()
         && time1.seconds() === time2.seconds()
    }
  }
}
</script>

<style>

</style>
