<template>
  <v-menu
    v-model="menu"
    ref="menu"
    transition="scale-transition"
    offset-y
    min-width="290px"
    :return-value.sync="dates"
    :close-on-content-click="false"
  >
    <template v-slot:activator="{ on }">
      <v-text-field
        :value="formatedDate"
        label="Zeitraum"
        prepend-icon="event"
        readonly
        color="blue"
        v-on="on"
        show-week
      ></v-text-field>
    </template>
    <v-date-picker
      v-model="dates"
      locale="ch-de"
      color="blue"
      first-day-of-week="1"
      ref="picker"
      show-week
      range
      no-title
    >
      <v-spacer></v-spacer>
      <v-btn text color="blue" @click="menu = false">Cancel</v-btn>
      <v-btn text color="blue" @click="$refs.menu.save(dates); save()">OK</v-btn>
    </v-date-picker>
  </v-menu>
</template>

<script>
export default {
  props: {
    value: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      menu: false,
      dates: [
        this.$moment(new Date())
          .subtract(2, 'weeks')
          .format('YYYY-MM-DD'),
        this.$moment(new Date()).format('YYYY-MM-DD')
      ]
    }
  },
  computed: {
    formatedDate() {
      return `${this.$moment(this.dates[0]).format('DD.MM.YYYY')} - ${this.$moment(this.dates[1]).format('DD.MM.YYYY')}`
    }
  },
  watch: {
    value() {
      this.setDates()
    }
  },
  mounted() {
    this.setDates()
  },
  methods: {
    setDates() {
      if (this.$moment.isMoment(this.value[0]) && this.$moment.isMoment(this.value[1])) {
        this.dates = [this.value[0].format('YYYY-MM-DD'), this.value[1].format('YYYY-MM-DD')]
      }
    },
    save() {
      const date1 = this.$moment(this.dates[0])
      const date2 = this.$moment(this.dates[1])
      if (date1.isBefore(date2)) this.$emit('input', [date1, date2])
      else this.$emit('input', [date2, date1])
    }
  }
}
</script>

<style>
</style>
