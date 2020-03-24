<template>
  <time-type-form
    :open="open"
    @save="$refs.form.save()"
    @cancel="open = false"
    @deleteTimerecord="$refs.form.deleteTimerecord()"
    class="no-select"
    :edit="!!timerecord"
  >
    <timerecord-form
      v-if="open"
      ref="form"
      :start-time="startTime"
      :date="day.date"
      :url-worker-param="urlWorkerParam"
      :timerecord="timerecord"
      @updated="updated"
    ></timerecord-form>
  </time-type-form>
</template>

<script>
import TimeTypeForm from '@/components/Time/TimeTypeForm'
import TimerecordForm from '@/components/Time/TimerecordForm'

export default {
  name: 'TimePopup',
  components: {
    TimeTypeForm,
    TimerecordForm
  },
  props: {
    date: String,
    value: Boolean,
    settings: Object,
    urlWorkerParam: String
  },
  data() {
    return {
      open: false,
      startTime: null,
      day: null,
      timerecord: null
    }
  },
  methods: {
    openNewTimerecord({ day, selectedStartHour, timerecord }) {
      this.startTime = selectedStartHour
      this.day = day
      this.timerecord = timerecord
      this.open = false
      setTimeout(() => {
        this.open = true
      }, 100)
    },
    updated(newTimerecords) {
      this.day.hours = newTimerecords
      this.$emit('updated', this.day)
      this.open = false
    }
  }
}
</script>

<style lang="scss" scoped>
.no-select {
  user-select: none;
}

.comment-textarea {
  margin-bottom: 50px;
}

.delete-button {
  margin-bottom: 70px;
}
</style>
