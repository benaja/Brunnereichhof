<template>
  <draggable-card
    :data-rapportdetail-id="value.id"
    :data-employee-id="employee.id"
    class="teal lighten-5"
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
    value: {
      type: Object,
      required: true
    }
  },
  computed: {
    employee() {
      return this.value.employee
    }
  }
}
</script>

<style>

</style>
