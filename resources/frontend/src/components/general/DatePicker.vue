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
        :prepend-inner-icon="outlined && !noIcon ? 'event' : null"
        :prepend-icon="outlined || noIcon ? null : 'event'"
        :rules="rules"
        :color="color"
        :outlined="outlined"
        :dense="dense"
        :full-width="fullWidth"
        show-week
        readonly
        v-on="on"
      ></v-text-field>
    </template>
    <v-date-picker
      ref="picker"
      v-model="date"
      locale="ch-de"
      :color="color"
      :first-day-of-week="1"
      :type="type === 'year' ? 'date' : type"
      :max="
        type === 'year'
          ? $moment()
            .add(3, 'years')
            .format('YYYY-MM-DD')
          : max
      "
      show-week
      :min="min"
      @input="model = false"
    ></v-date-picker>
  </v-menu>
</template>

<script>
export default {
  props: {
    value: {
      type: [String, Number],
      default: null
    },
    label: {
      type: String,
      default: null
    },
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
    },
    min: {
      type: String,
      default: undefined
    },
    max: {
      type: String,
      default: undefined
    },
    noIcon: {
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
  computed: {
    date: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    }
  },
  watch: {
    model(val) {
      if (this.type === 'year') {
        this.$nextTick(() => { this.$refs.picker.activePicker = 'YEAR' })
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
  },
  mounted() {
    this.setFormatedDate()
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
  }
}
</script>
