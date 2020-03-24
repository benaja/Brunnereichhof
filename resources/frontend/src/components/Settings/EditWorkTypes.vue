<template>
  <v-expansion-panels>
    <v-expansion-panel v-for="worktype of worktypes" :key="worktype.id">
      <v-expansion-panel-header>{{ worktype.name_de }}</v-expansion-panel-header>
      <v-expansion-panel-content>
        <edit-work-type :value="worktype"></edit-work-type>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
import EditWorkType from '@/components/Settings/EditWorkType'

export default {
  components: {
    EditWorkType
  },
  data() {
    return {
      worktypes: []
    }
  },
  mounted() {
    this.axios.get('/worktypes').then(response => {
      this.worktypes = response.data
    })
  }
}
</script>

<style></style>
