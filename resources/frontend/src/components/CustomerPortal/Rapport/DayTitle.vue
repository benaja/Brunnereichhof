<template>
  <p
    class="font-weight-bold"
    :style="{ gridArea }"
    :class="{'display-1': $vuetify.breakpoint.smAndDown }"
  >{{text}}</p>
</template>

<script>
export default {
  props: {
    text: {
      type: String,
      default: null
    },
    gridArea: {
      type: String,
      default: null
    }
  }
}
</script>
