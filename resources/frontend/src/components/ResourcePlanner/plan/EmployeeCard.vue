<template>
  <draggable-card
    :name="employee.name"
    :image="employee.small_profile_image"
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
