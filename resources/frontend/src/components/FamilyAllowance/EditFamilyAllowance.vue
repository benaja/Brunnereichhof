<template>
  <v-card v-if="value">
    <h3 class="pa-4 title">
      {{ value.family_allowanceable.lastname }} {{ value.family_allowanceable.firstname }}
      <span class="float-right">
        <v-btn
          icon
          @click="$emit('close')"
        >
          <v-icon>close</v-icon>
        </v-btn>
      </span>
    </h3>
    <div
      v-if="value"
      class="card-content"
    >
      <FamilyAllowanceForm
        v-model="value"
        :parent="value.family_allowanceable"
        :model="value.family_allowanceable_type"
      ></FamilyAllowanceForm>
    </div>
  </v-card>
</template>

<script>
import FamilyAllowanceForm from '@/components/FamilyAllowance/FamilyAllowanceForm'

export default {
  components: {
    FamilyAllowanceForm
  },
  props: {
    value: {
      type: Object,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.card-content {
  max-height: calc(100vh - 300px);
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px
}
</style>
