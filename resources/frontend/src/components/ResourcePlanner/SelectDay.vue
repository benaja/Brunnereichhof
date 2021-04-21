<template>
  <div class="d-flex justify-between">
    <div class="mr-6">
      <p class="mx-0 mt-3 text-center">
        <v-btn
          text
          icon
          @click="previousDay"
        >
          <v-icon>keyboard_arrow_left</v-icon>
        </v-btn>
      </p>
    </div>
    <div>
      <v-dialog
        v-model="dateDialog"
        width="290px"
        class="text-center"
      >
        <template v-slot:activator="{ on }">
          <div
            class="date-picker-activator"
            v-on="on"
          >
            <p class="text-center overline mb-0">
              {{ $moment(date).format('dddd') }}
            </p>
            <p class="text-center headline mb-0">
              {{ $moment(date).format('DD') }}
            </p>
            <p class="text-center overline mb-0">
              {{ $moment(date).format('MMM') }}
            </p>
          </div>
        </template>
        <v-date-picker
          v-model="date"
          scrollable
          first-day-of-week="1"
          locale="ch-de"
          show-week
          @change="dateDialog = false"
        ></v-date-picker>
      </v-dialog>
    </div>
    <div class="ml-6">
      <p class="mx-0 mt-3 text-center">
        <v-btn
          text
          icon
          @click="nextDay"
        >
          <v-icon>keyboard_arrow_right</v-icon>
        </v-btn>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      date: this.value,
      dateDialog: false
    }
  },
  watch: {
    value() {
      this.date = this.value
    },
    date() {
      this.$emit('input', this.date)
    }
  },
  methods: {
    nextDay() {
      const tomorrow = this.$moment(this.date, 'YYYY-MM-DD').add(1, 'day')
      this.date = tomorrow.format('YYYY-MM-DD')
    },
    previousDay() {
      const yesterday = this.$moment(this.date, 'YYYY-MM-DD').subtract(1, 'day')
      this.date = yesterday.format('YYYY-MM-DD')
    }
  }
}
</script>

<style lang="scss" scoped>
.date-picker-activator {
  cursor: pointer;
  width: 70px
}
</style>
