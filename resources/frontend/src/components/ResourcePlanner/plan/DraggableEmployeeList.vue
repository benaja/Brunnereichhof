<template>
  <draggable
    :value="value"
    group="employees"
    class="elevation-1"
  >
    <employee-card
      v-for="employee of value"
      :key="employee.id"
      :employee="employee"
    ></employee-card>
  </draggable>
</template>

<script>
import EmployeeCard from '@/components/ResourcePlanner/plan/EmployeeCard'
import Draggable from 'vuedraggable'

export default {
  components: {
    EmployeeCard,
    Draggable
  },
  props: {
    value: {
      type: Array,
      default: () => []
    }
  }
}
</script>

<style>

</style>
