<template>
  <v-card class="mt-4">
    <v-list-item>
      <v-list-item-avatar
        left
        size="80"
        tile
        color="primary"
        class="avatar"
      >
        <v-icon
          color="white"
          large
        >
          {{ icon }}
        </v-icon>
      </v-list-item-avatar>
      <v-list-item-content class="list-content">
        <h3 class="body-2 text-right">
          {{ title }}
        </h3>
        <p class="text-right headline">
          {{ value }}
        </p>
      </v-list-item-content>
    </v-list-item>
  </v-card>
</template>

<script>
export default {
  name: 'SinleStatCard',
  props: {
    icon: {
      type: String,
      default: null
    },
    title: {
      type: String,
      default: null
    },
    value: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.avatar {
  margin-top: -40px;
  border-radius: 10px;
}

.list-content {
  height: 110px;
}
</style>
