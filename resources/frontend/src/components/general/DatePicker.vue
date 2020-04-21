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
        v-on="on"
        :value="formatedDate"
        :label="label"
        :prepend-inner-icon="outlined ? 'event' : null"
        :prepend-icon="outlined ? null : 'event'"
        :rules="rules"
        :color="color"
        :outlined="outlined"
        :dense="dense"
        :full-width="fullWidth"
        show-week
        readonly
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
      :max="
        type === 'year'
          ? $moment()
              .add(3, 'years')
              .format('YYYY-MM-DD')
          : undefined
      "
      show-week
      :locale-first-day-of-year="1"
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
    },
    dense: {
      type: Boolean,
      default: false
    },
    fullWidth: {
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
    formatDate(format, parseFormat = undefined) {
      return this.date ? this.$moment(this.date, parseFormat).format(format) : ''
    },
    setFormatedDate() {
      if (this.type === 'date') this.formatedDate = this.formatDate('DD.MM.YYYY')
      else if (this.type === 'month') this.formatedDate = this.formatDate('MM.YYYY')
      else this.formatedDate = this.formatDate('YYYY', 'YYYY')
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
