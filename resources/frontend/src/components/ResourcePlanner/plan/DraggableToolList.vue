<template>
  <draggable
    :list="internalValue"
    :data-customer-id="customerId"
    group="tools"
    class="elevation-1"
  >
    <tool-card
      v-for="tool of internalValue"
      :key="tool.id"
      :tool="tool"
    ></tool-card>
  </draggable>
</template>

<script>
import ToolCard from '@/components/ResourcePlanner/plan/ToolCard'
import Draggable from 'vuedraggable'

export default {
  components: {
    ToolCard,
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
