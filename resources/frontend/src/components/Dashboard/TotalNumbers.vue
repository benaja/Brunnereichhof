<template>
  <v-card
    max-width="100%"
    class="card"
    color="primary"
    dark
    height="260"
  >
    <v-card-text>
      <div
        v-for="(stat, index) of stats"
        :key="index"
      >
        <div class="display-1 font-weight-thin text-center">
          {{ stat.text }}
        </div>
        <p class="text-center headline">
          {{ stat.value }}
        </p>
      </div>
    </v-card-text>
    <v-card-text></v-card-text>
  </v-card>
</template>

<script>
export default {
  name: 'TotalNumbers',
  props: {
    stats: {
      type: Array,
      default: null
    }
  }
}
</script>
