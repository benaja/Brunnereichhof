<template>
  <v-expansion-panels
    v-if="value && $auth.user().hasPermission('superadmin', 'family_allowance_read')"
    class="my-10"
  >
    <v-expansion-panel
      :readonly="!$auth.user().hasPermission('superadmin', 'employee_read')"
    >
      <v-expansion-panel-header hide-actions>
        <h3>{{ $t('Familienzulagen') }}</h3>
      </v-expansion-panel-header>
      <v-expansion-panel-content>
        <FamilyAllowanceForm
          v-model="value"
          :parent="parent"
          :model="model"
        ></FamilyAllowanceForm>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
import FamilyAllowanceForm from './FamilyAllowanceForm'

export default {
  components: {
    FamilyAllowanceForm
  },
  props: {
    value: {
      type: Object,
      default: null
    },
    parent: {
      type: Object,
      default: null
    },
    model: {
      type: String,
      required: true
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
