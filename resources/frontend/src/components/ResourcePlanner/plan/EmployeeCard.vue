<template>
  <draggable-card
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
    employee: {
      type: Object,
      required: true
    }
  }
}
</script>

<style>

</style>
