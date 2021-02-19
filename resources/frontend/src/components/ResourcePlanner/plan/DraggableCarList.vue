<template>
  <draggable
    :list="internalValue"
    group="cars"
    class="elevation-1"
  >
    <car-card
      v-for="car of internalValue"
      :key="car.id"
      :car="car"
    ></car-card>
  </draggable>
</template>

<script>
import CarCard from '@/components/ResourcePlanner/plan/CarCard'
import Draggable from 'vuedraggable'


export default {
  components: {
    CarCard,
    Draggable
  },
  props: {
    value: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      internalValue: this.value
    }
  },
  watch: {
    internalValue() {
      this.$emit('input', this.internalValue)
    },
    value() {
      this.internalValue = this.value
    }
  }
}
</script>

<style>

</style>
