<template>
  <draggable-card
    :data-car-id="car.id"
    :name="car.name"
    :image="car.small_image"
  ></draggable-card>
</template>

<script>
import DraggableCard from './DraggableCard'

export default {
  components: {
    DraggableCard
  },
  props: {
    car: {
      type: Object,
      required: true
    }
  }
}
</script>

<style>

</style>
