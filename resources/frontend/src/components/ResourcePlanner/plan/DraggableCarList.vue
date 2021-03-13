<template>
  <draggable
    :value="internalValue"
    :data-customer-id="customerId"
    :group="{name: 'resource-planner', pull: 'clone', put: canPut}"
    class="elevation-1"
    @add="add"
    @remove="remove"
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
import { confirmAction } from '@/utils'

export default {
  components: {
    CarCard,
    Draggable
  },
  props: {
    value: {
      type: Array,
      default: () => []
    },
    customerId: {
      type: Number,
      default: null
    },
    usedCarIds: {
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
  },
  methods: {
    add(value) {
      const { carId } = value.item.dataset

      this.$emit('add', carId)
    },
    remove(value) {
      const toCustomerId = value.to.dataset.customerId
      const { carId } = value.item.dataset

      if (!toCustomerId) {
        this.$emit('remove', carId)
      }
    },

    canPut(to, from, item) {
      return !!item.dataset.carId
    }
  }
}
</script>

<style>

</style>
