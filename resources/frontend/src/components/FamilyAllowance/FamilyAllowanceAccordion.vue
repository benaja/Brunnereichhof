<template>
  <v-expansion-panels class="my-10">
    <v-expansion-panel
      :readonly="!$auth.user().hasPermission('superadmin', 'employee_read')"
    >
      <v-expansion-panel-header hide-actions>
        <h3>{{ $t('Familienzulagen') }}</h3>
      </v-expansion-panel-header>
      <v-expansion-panel-content>
        <p>Content</p>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
export default {

}
</script>

<style lang="scss" scoped>

</style>
