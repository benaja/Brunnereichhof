<template>
  <v-card class="mt-4">
    <v-list-item>
      <v-list-item-avatar left size="80" tile color="primary" class="avatar">
        <v-icon color="white" large>{{icon}}</v-icon>
      </v-list-item-avatar>
      <v-list-item-content class="list-content">
        <h3 class="body-2 text-right">{{title}}</h3>
        <p class="text-right headline">{{value}}</p>
      </v-list-item-content>
    </v-list-item>
    <v-card-text class="pt-0">
      <v-divider></v-divider>
    </v-card-text>
    <v-card-actions>
      <p class="caption ml-2">
        <v-icon class="mr-1">{{actionIcon}}</v-icon>
        <template v-if="updatedAt">Vor {{lastUpdatedInMinutes}} Minuten aktualisiert</template>
        <template v-else>{{actionText}}</template>
      </p>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: 'SinleStatCard',
  props: {
    icon: String,
    title: String,
    value: String,
    actionIcon: {
      type: String,
      default: 'schedule'
    },
    actionText: String,
    updatedAt: String
  },
  computed: {
    lastUpdatedInMinutes() {
      let date = this.$moment(this.updatedAt).add(1, 'hour')
      return this.$moment().diff(date, 'minutes')
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
