<template>
  <v-card class="mt-4">
    <v-card-title>
      <div
        class="chart elevation-5"
        :class="color"
      >
        <line-chart
          :chart-data="chartData"
          :options="options"
          :height="null"
        ></line-chart>
      </div>
      <h3 class="headline">
        {{ title }}
      </h3>
    </v-card-title>
    <v-card-text>
      <p>{{ text }}</p>
    </v-card-text>
  </v-card>
</template>

<script>
import LineChart from '@/components/general/LineChart'

export default {
  name: 'StatsCard',
  components: {
    LineChart
  },
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
    datasets: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      options: {
        gridLines: {
          color: 'white'
        },
        legend: {
          display: false,
          labels: {
            fontColor: 'white',
            fontSize: 18
          }
        },
        maintainAspectRatio: true,
        aspectRatio: 2,
        scales: {
          xAxes: [
            {
              gridLines: {
                display: false
              },
              ticks: {
                fontColor: 'white'
              }
            }
          ],
          yAxes: [{
            gridLines: {
              color: 'rgba(255, 255, 255, 0.5)',
              zeroLineColor: 'rgba(255, 255, 255, 0.5)'
            },
            ticks: {
              fontColor: 'white'
            }
          }]
        }
      }
    }
  },
  computed: {
    chartData() {
      return {
        labels: this.datasets[0].map(item => item.name),
        datasets: this.datasets.map((set, index) => ({
          label: index === 0 ? 'Aktuelles Jahr' : 'Vorheriges Jahr',
          borderColor: index === 0 ? 'white' : '#b5b5b5',
          backgroundColor: index === 0 ? 'rgba(255, 255, 255, 0.3)' : 'rgba(168, 168, 168, 0.3)',
          data: set.map(item => item.hours)
        }))
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
  // height: 400px;
  margin-top: -30px;
  border-radius: 10px;
  padding: 30px;
}

h3 {
  margin-top: 10px;
}
</style>
