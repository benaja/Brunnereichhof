<template>
  <div class="day">
    <p class="date hidden-sm-and-down">{{formatedDate}}</p>
    <div class="hour" v-for="index in numbers" :key="index" @mouseup="$emit('add', date)">
      <v-divider :class="{'mr-2': $store.getters.isMobile}"></v-divider>
    </div>
    <v-divider class="mr-2"></v-divider>
    <div class="time-elements">
      <time-element
        v-for="timerecord of value"
        :key="timerecord.id"
        :value="timerecord"
        @edit="$emit('edit', timerecord)"
      ></time-element>
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
    value: Array
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
}

.hour {
  height: 8vh;
  cursor: pointer;
  user-select: none;
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
</style>
