<template>
  <v-menu
    v-model="model"
    :nudge-right="40"
    transition="scale-transition"
    offset-y
    min-width="290px"
    :close-on-content-click="false"
  >
    <template v-slot:activator="{ on }">
      <v-text-field
        v-bind="$attrs"
        :value="formatedDate"
        readonly
        v-on="on"
      ></v-text-field>
    </template>
    <div>
      <div
        v-if="yearSelected"
        class="quarter-container"
      >
        <v-btn
          v-for="(quarter, index) of quarters"
          :key="quarter"
          text
          color="primary"
          @click="selectQuarter(index)"
        >
          {{ quarter }}
        </v-btn>
      </div>
      <v-date-picker
        v-else
        ref="picker"
        v-model="date"
        locale="ch-de"
        reactive
        @input="updateYear"
      ></v-date-picker>
    </div>
  </v-menu>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    value: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      model: false,
      formatedDate: '',
      date: null,
      quarters: [
        'Q1',
        'Q2',
        'Q3',
        'Q4'
      ],
      yearSelected: false
    }
  },
  watch: {
    model() {
      if (this.model) {
        this.$nextTick(() => {
          if (this.$refs.picker) {
            this.$refs.picker.activePicker = 'YEAR'
          }
        })
      }
    },
    value() {
      this.date = this.value
    },
    date() {
      const date = this.$moment(this.date)
      this.formatedDate = `Q${date.quarter()} ${date.format('YYYY')}`
    }
  },
  mounted() {
    this.date = this.value
  },
  methods: {
    updateYear() {
      this.model = false
      this.$nextTick(() => {
        this.yearSelected = true
        this.model = true
      })
    },
    selectQuarter(quarter) {
      const date = this.$moment(this.date)
        .month((quarter + 1) * 3 - 1)
        .endOf('month')

      this.date = date.format('YYYY-MM-DD')
      this.yearSelected = false
      this.model = false

      this.$emit('input', this.date)
    }
  }

}
</script>

<style lang="scss" scoped>
.quarter-container {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  background-color: white;

  > button {
    width: 50%;
  }
}
</style>
