<template>
  <div :style="{ gridArea }">
    <p v-if="$vuetify.breakpoint.smAndDown" class="overline mb-0">Kommentar</p>
    <p class="mb-8">{{text}}</p>
  </div>
</template>

<script>
export default {
  props: {
    gridArea: {
      type: String,
      default: null
    },
    text: {
      type: String,
      default: null
    }
  }
}
</script>

<style>
</style>
