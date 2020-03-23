<template>
  <v-menu
    :close-on-content-click="false"
    offset-y
    v-model="open"
    :position-x="positionX"
    :position-y="positionY"
    z-index="101"
    nudge-width="400"
    max-width="400"
  >
    <v-card>
      <v-card-actions>dfasdf</v-card-actions>
      <h1>Add Element</h1>
    </v-card>
  </v-menu>
</template>

<script>
export default {
  data() {
    return {
      positionX: 0,
      positionY: 0,
      open: false
    }
  },
  methods: {
    openNewTimerecord(date, index, event, pixelPerHour) {
      this.positionY = event.clientY
      const daysContainer = document.getElementsByClassName('days')[0]
      const rect = daysContainer.getBoundingClientRect()
      const relative = daysContainer.offsetWidth - event.target.parentElement.offsetWidth * (6 - index)
      let positionX = rect.left + relative
      if (window.innerWidth < positionX + 400) {
        positionX -= 400 + event.target.parentElement.offsetWidth
      }
      this.positionX = positionX
      setTimeout(() => {
        this.open = true
      }, 100)
    }
  }
}
</script>

<style></style>
