<template>
  <draggable-card
    :data-rapportdetail-id="value.id"
    :data-employee-id="employee.id"
    :name="`${employee.lastname} ${employee.firstname}`"
    :image="employee.small_profile_image"
    class="teal lighten-5"
  ></draggable-card>
</template>

<script>
import DraggableCard from './DraggableCard'

export default {
  components: {
    DraggableCard
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
