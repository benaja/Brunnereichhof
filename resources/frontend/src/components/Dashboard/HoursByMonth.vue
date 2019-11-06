<template>
  <v-card max-width="100%" class="card" color="primary" dark min-height="260">
    <v-card-text>
      <div class="display-1 font-weight-thin text-center mb-2">Geleistete Stunden pro Monat</div>
    </v-card-text>
    <v-card-text>
      <v-sheet color="primary">
        <v-sparkline
          v-if="values.length > 0"
          color="white"
          :value="values"
          line-width="2"
          stroke-linecap="round"
          smooth
          padding="16"
          auto-draw
          show-labels
        >
          <template slot="label" slot-scope="item">{{item.value}}</template>
        </v-sparkline>
      </v-sheet>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: 'HoursByMonth',
  props: {
    stats: {
      default: () => {}
    }
  },
  data() {
    return {
      values: [],
      labels: []
    }
  },
  watch: {
    stats() {
      this.values = []
      this.labels = []
      for (let month of this.stats) {
        this.values.push(month.hours)
        this.labels.push(month.name)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.card {
  margin: 0 auto;
}
</style>
