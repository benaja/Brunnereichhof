<template>
  <draggable-card
    :data-car-id="car.id"
    :name="car.name"
    :avatar="car.small_image_url"
    :image="car.image_url"
    class="blue lighten-5"
    @rightClick="$emit('rightClick', $event)"
  >
    <template slot="name">
      <p class="mb-0">
        <span class="font-weight-bold">{{ car.name }}</span>
        <span> {{ car.seats }} Sitze</span>
      </p>
    </template>
  </draggable-card>
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
