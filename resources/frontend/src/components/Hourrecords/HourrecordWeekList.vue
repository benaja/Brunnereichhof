<template>
  <div class="px-3">
    <div v-for="(hourrecord, index) of hourrecords" :key="index" class="week-item">
      <v-list-item :to="'/hourrecords/' + hourrecord.year + '/' + hourrecord.week">
        <v-list-item-content class="pt-2">
          <p class="mb-0 week-text">
            <span class="font-weight-bold">KW {{hourrecord.week}}</span>
            ({{getMondayOfWeek(hourrecord.week, hourrecord.year).toLocaleDateString()}} -
            {{getSundayOfWeek(hourrecord.week, hourrecord.year).toLocaleDateString()}})
            / {{hourrecord.hours}} Stunden
          </p>
        </v-list-item-content>
        <v-list-item-action class="hidden-xs-only">
          <v-icon>send</v-icon>
        </v-list-item-action>
      </v-list-item>
      <v-divider class="divider" v-if="index < hourrecords.length -1"></v-divider>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    hourrecords: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    getMondayOfWeek(week, year) {
      let day = (week - 1) * 7
      return new Date(year, 0, day)
    },
    getSundayOfWeek(week, year) {
      let monday = this.getMondayOfWeek(week, year)
      monday.setDate(monday.getDate() + 6)
      return monday
    }
  }
}
</script>

<style>
</style>
