<template>
  <div class="progress-bar-container">
    <v-progress-linear v-if="loading" indeterminate :color="color"></v-progress-linear>
  </div>
</template>

<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: 'primary'
    }
  }
}
</script>

<style lang="scss" scoped>
.progress-bar-container {
  height: 4px;
}
</style>
