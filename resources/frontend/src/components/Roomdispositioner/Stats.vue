<template>
  <div>
    <h2>Statistiken</h2>
    <v-divider class="mb-2"></v-divider>
    <p>
      <span class="font-weight-bold">Freie Plätze:</span>
      <span class="float-right">{{stats.freePlaces}}</span>
    </p>
    <p>
      <span class="font-weight-bold">Besetzte Plätze:</span>
      <span class="float-right">{{stats.usedPlaces}}</span>
    </p>
    <p>
      <span class="font-weight-bold">Totale Plätze:</span>
      <span class="float-right">{{stats.totalPlaces}}</span>
    </p>
  </div>
</template>

<script>
export default {
  name: 'Stats',
  data() {
    return {
      stats: {}
    }
  },
  mounted() {
    this.getStats()
  },
  methods: {
    getStats() {
      this.axios.get('/stats/roomdispositioner').then(response => {
        this.stats = response.data
      })
    }
  }
}
</script>

<style>
</style>
