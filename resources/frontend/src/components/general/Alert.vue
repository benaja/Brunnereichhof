<template>
  <div class="alert">
    <v-alert
      :value="value"
      :color="color"
      icon="check_circle"
      transition="slide-y-reverse-transition"
    >{{text}}</v-alert>
  </div>
</template>

<script>
export default {
  name: 'Alert',
  props: {
    value: Boolean,
    text: String,
    color: {
      type: String,
      default: 'success'
    }
  },
  watch: {
    value() {
      setTimeout(() => {
        this.$emit('input', false)
      }, 2000)
    }
  }
}
</script>

<style lang="scss" scoped>
.alert {
  position: fixed;
  bottom: 0;
  right: 5px;
  width: 40%;
  z-index: 10;
}
</style>
