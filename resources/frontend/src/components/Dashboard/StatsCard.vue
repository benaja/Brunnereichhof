<template>
  <v-card class="mt-4">
    <v-card-title>
      <div
        class="chart elevation-5"
        :class="color"
      >
        <chartist
          ratio="ct-major-second"
          type="Line"
          :data="chartData"
          :options="options"
        ></chartist>
      </div>
      <h3 class="headline">
        {{ title }}
      </h3>
    </v-card-title>
    <v-card-text>
      <p>{{ text }}</p>
      <v-divider></v-divider>
    </v-card-text>
    <v-card-actions>
      <p class="caption ml-2">
        <v-icon>schedule</v-icon>
        Vor {{ lastUpdatedInMinutes }} Minuten aktualisiert
      </p>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: 'StatsCard',
  props: {
    title: {
      type: String,
      required: true
    },
    text: {
      type: String,
      default: null
    },
    color: {
      type: String,
      default: 'primary'
    },
    updatedAt: {
      type: String,
      default: null
    },
    dataset: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      options: {
        low: 0,
        chartPadding: {
          top: 0,
          right: 0,
          bottom: 0,
          left: 0
        },
        height: '190px'
      }
    }
  },
  computed: {
    lastUpdatedInMinutes() {
      const date = this.$moment(this.updatedAt).add(1, 'hour')
      return this.$moment().diff(date, 'minutes')
    },
    chartData() {
      const labels = []
      const series = []
      for (let i = 0; i < this.dataset.length; i++) {
        labels.push(this.dataset[i].name)
        series.push(this.dataset[i].hours)
      }
      return {
        labels,
        series: [series]
      }
    }
  },
  watch: {
    stats() {
      this.values = []
      this.labels = []
      for (const month of this.stats) {
        this.values.push(month.hours)
        this.labels.push(month.name)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.chart {
  width: 100%;
  height: 200px;
  margin-top: -30px;
  border-radius: 10px;
  padding-top: 10px;
  padding-right: 10px;
}

h3 {
  margin-top: 10px;
}
</style>

<style lang="scss">
.ct-series-a .ct-point,
.ct-series-a .ct-line,
.ct-series-a .ct-bar,
.ct-series-a .ct-slice-donut {
  stroke: white !important;
}

.ct-labels span {
  width: 40px !important;
  margin-right: 5px;
}
</style>
