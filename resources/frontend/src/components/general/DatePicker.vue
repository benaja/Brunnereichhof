<template>
  <v-menu
    v-model="model"
    :nudge-right="40"
    transition="scale-transition"
    offset-y
    min-width="290px"
    :close-on-content-click="type === 'year'"
  >
    <template v-slot:activator="{ on }">
      <v-text-field
        :value="formatedDate"
        :label="label"
        prepend-icon="event"
        readonly
        :rules="rules"
        :color="color"
        v-on="on"
        show-week
      ></v-text-field>
    </template>
    <v-date-picker
      v-model="date"
      @input="model = false"
      locale="ch-de"
      :color="color"
      first-day-of-week="1"
      ref="picker"
      :type="type === 'year' ? 'date' : type"
      :max="type === 'year' ? $moment().add(3, 'years').format('YYYY-MM-DD') : undefined"
    ></v-date-picker>
  </v-menu>
</template>

<script>
export default {
  name: 'DatePicker',
  props: {
    value: [String, Number],
    label: String,
    rules: {
      type: Array,
      default: () => []
    },
    color: {
      type: String,
      default: 'primary'
    },
    type: {
      type: String,
      default: 'date'
    },
    outlined: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      model: false,
      formatedDate: ''
    }
  },
  mounted() {
    this.setFormatedDate()
  },
  computed: {
    date: {
      get: function() {
        return this.value
      },
      set: function(value) {
        this.$emit('input', value)
      }
    }
  },
  methods: {
    formatDate(format) {
      return this.date ? this.$moment(this.date).format(format) : ''
    },
    setFormatedDate() {
      if (this.type === 'date') this.formatedDate = this.formatDate('DD.MM.YYYY')
      else if (this.type === 'month') this.formatedDate = this.formatDate('MM.YYYY')
      else this.formatedDate = this.formatDate('YYYY')
    }
  },
  watch: {
    model(val) {
      if (this.type === 'year') {
        val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
        if (!val) {
          this.$emit('input', `${this.$refs.picker.inputYear}-01-01`)
        }
      }
    },
    date() {
      this.setFormatedDate()
    },
    type() {
      this.setFormatedDate()
    }
  }
}
</script>

<style>
</style>
