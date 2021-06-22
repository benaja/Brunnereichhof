<template>
  <draggable
    :value="value"
    group="resource-planner"
    class="elevation-1"
  >
    <employee-card
      v-for="employee of value"
      :key="employee.id"
      :employee="employee"
      @rightClick="$emit('add', employee.id)"
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
    },
    selectedResource: {
      type: Object,
      default: null
    }
  }
}
</script>

<style>

</style>
