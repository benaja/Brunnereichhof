<template>
  <draggable-card
    :data-employee-id="employee.id"
    class="teal lighten-5"
    @rightClick="$emit('rightClick', $event)"
  >
    <employee-card-content :employee="employee"></employee-card-content>
  </draggable-card>
</template>

<script>
import DraggableCard from './DraggableCard'
import EmployeeCardContent from './EmployeeCardContent'

export default {
  components: {
    DraggableCard,
    EmployeeCardContent
  },

  props: {
    employee: {
      type: Object,
      required: true
    }
  }
}
</script>
