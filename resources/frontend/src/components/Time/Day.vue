<template>
  <div class="day">
    <v-divider vertical class="float-left"></v-divider>
    <p class="date hidden-sm-and-down">{{ formatedDate }}</p>
    <div class="time-elements">
      <div class="time-elements-container" @mouseup="openTimePopup">
        <time-element
          v-for="timerecord of value"
          :key="timerecord.id"
          :value="timerecord"
          @edit="$emit('edit', timerecord)"
          :url-worker-param="urlWorkerParam"
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
      const elementsContainerRect = event.target.getBoundingClientRect()
      console.log(event.clientY - elementsContainerRect.top)
      // console.log(event)
    }
  },
  computed: {
    formatedDate() {
      let date = new Date(this.date)
      let day = date.getDay() === 0 ? 7 : date.getDay()
      day--
      return `${this.$store.getters.dayShortNames[day]}, ${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}`
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
