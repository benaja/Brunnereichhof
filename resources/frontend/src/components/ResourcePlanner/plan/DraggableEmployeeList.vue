<template>
  <draggable
    :list="internalValue"
    group="employees"
    class="elevation-1"
    @change="change"
  >
    <employee-card
      v-for="employee of internalValue"
      :key="employee.id"
      :employee="employee"
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
    change(event) {
      if (event.added) {
        this.$emit('add', event.added.element)
      }
      if (event.removed) {
        this.$emit('remove', event.removed.element)
      }
    }
  }
}
</script>

<style>

</style>
