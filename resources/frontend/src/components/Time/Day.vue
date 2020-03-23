<template>
  <div class="day">
    <v-divider vertical class="float-left"></v-divider>
    <div class="time-elements">
      <div class="time-elements-container" ref="timeElementsCointainer" @mouseup="openTimePopup">
        <time-element
          v-for="timerecord of value"
          :key="timerecord.id"
          :value="timerecord"
          @edit="$emit('edit', timerecord)"
          :url-worker-param="urlWorkerParam"
        ></time-element>
      </div>
    </div>
    <v-btn fab color="primary" class="overview-button hidden-md-and-up" @click="$emit('openOveriew')">
      <v-icon>assessment</v-icon>
    </v-btn>
  </div>
</template>

<script>
import TimeElement from '@/components/Time/TimeElement'

export default {
  name: 'Day',
  components: {
    TimeElement
  },
  props: {
    date: String,
    settings: Object,
    value: Array,
    urlWorkerParam: String
  },
  data() {
    return {
      isAddTimeOpen: false,
      isEditTimeOpen: false,
      time: '07:00',
      currentTimeRecord: {},
      numbers: [3, 4, 5, 6, 7, 8, 9, 10]
    }
  },
  methods: {
    edit(timerecord) {
      this.currentTimeRecord = timerecord
      this.isAddTimeOpen = true
    },
    openTimePopup(event) {
      event.preventDefault()
      const elementsContainerRect = event.target.getBoundingClientRect()
      const relativeHeight = event.clientY - elementsContainerRect.top
      const hour = relativeHeight / this.pixelPerHour
      const selectedStartHour = Math.floor(hour * 2) / 2
      this.$emit('add', selectedStartHour, event, this.pixelPerHour)
    }
  },
  computed: {
    pixelPerHour() {
      return this.$refs.timeElementsCointainer.clientHeight / 24
    }
  }
}
</script>

<style lang="scss" scoped>
.day {
  position: relative;
  padding-top: 0.1px;
  width: calc(100% / 7.01);
  height: calc(50px * 24);
}

.hour {
  height: 50px;
  cursor: pointer;
  user-select: none;
  z-index: 5;
}

.overview-button {
  position: fixed;
  bottom: 10px;
  right: 10px;
}

.date {
  margin-top: -2.4em;
  margin-bottom: 1em;
  text-align: center;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.time-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.time-elements-container {
  position: relative;
  width: calc(100% - 8px);
  height: 100%;
}
</style>
