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
      const time = this.$moment(this.internalValue, 'HH:mm')
      if (time.isValid()) {
        this.$emit('input', time.format('HH:mm'))
      }
    },
    value() {
      const newTime = this.$moment(this.value, 'HH:mm')
      const time = this.$moment(this.internalValue, 'HH:mm')

      // only update internal time, when new time is different
      if (time.isValid()
        && (newTime.minutes() !== time.minutes() || newTime.seconds() !== time.seconds())) {
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
  }
}
</script>

<style>

</style>
