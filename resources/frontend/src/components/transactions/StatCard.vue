<template>
  <v-card class="pa-4 d-flex justify-space-between">
    <div>
      <p class="stat-label mb-0">
        {{ label }}
      </p>
      <p class="mb-0">
        <span class="stat-number">{{ amount | round }}</span> CHF
      </p>
    </div>
    <div class="align-self-center">
      <v-icon
        large
        color="primary"
      >
        {{ icon }}
      </v-icon>
    </div>
  </v-card>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      default: null
    },
    amount: {
      type: Number,
      default: null
    },
    icon: {
      type: String,
      default: 'arrow_upward'
    }
  }
}
</script>

<style lang="scss" scoped>
.stat-label {
  font-size: 1.1rem;
}

.stat-number {
  font-size: 2rem;
}
</style>
