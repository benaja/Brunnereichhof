<template>
  <div class="day">
    <v-divider
      vertical
      class="float-left"
    ></v-divider>
    <div class="time-elements">
      <div
        ref="timeElementsCointainer"
        class="time-elements-container"
        @click.self="openTimePopup"
      >
        <time-element
          v-for="timerecord of value"
          :key="timerecord.id"
          :value="timerecord"
          :url-worker-param="urlWorkerParam"
          @edit="$emit('update', { timerecord, pixelPerHour })"
          @scrolling="isScrolling => $emit('scrolling', isScrolling)"
        ></time-element>
      </div>
    </div>
    <v-btn
      fab
      color="primary"
      class="overview-button hidden-md-and-up"
      @click="$emit('openOveriew')"
    >
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
    date: {
      type: String,
      default: null
    },
    settings: {
      type: Object,
      default: null
    },
    value: {
      type: Array,
      default: null
    },
    urlWorkerParam: {
      type: String,
      default: null
    }
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
  computed: {
    pixelPerHour() {
      return this.$refs.timeElementsCointainer.clientHeight / 24
    }
  },
  methods: {
    edit(timerecord) {
      this.currentTimeRecord = timerecord
      this.isAddTimeOpen = true
    },
    openTimePopup(event) {
      const elementsContainerRect = event.target.getBoundingClientRect()
      const relativeHeight = event.clientY - elementsContainerRect.top
      const hour = relativeHeight / this.pixelPerHour
      const selectedStartHour = Math.floor(hour * 2) / 2
      this.$emit('update', { selectedStartHour, event, pixelPerHour: this.pixelPerHour })
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
  right: 10px;
  bottom: 10px;
  z-index: 5;
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
  margin-left: 4px;
}

@media only screen and (max-width: 960px) {
  .day {
    width: 100%;
  }
}
</style>
