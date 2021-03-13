<template>
  <draggable
    :value="internalValue"
    :data-customer-id="customerId"
    :group="{name: 'resource-planner', pull: 'clone', put: canPut}"
    :disabled="disabled"
    @add="add"
    @remove="remove"
  >
    <tool-card
      v-for="tool of internalValue"
      :key="tool.id"
      :tool="tool"
      :with-pivot="withPivot"
      :disabled="disabled"
      @add="addOne"
      @remove="removeOne"
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
    },
    availableTools: {
      type: Array,
      default: () => []
    },
    withPivot: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
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
      const { toolId, toolAmount } = value.item.dataset

      const alreadyExists = this.value.find(t => t.id === Number(toolId))
      if (alreadyExists) {
        this.$emit('increase', alreadyExists, toolAmount)
      } else {
        this.$emit('add', toolId, toolAmount)
      }
    },

    remove(value) {
      const toCustomerId = value.to.dataset.customerId
      const { toolId } = value.item.dataset

      if (!toCustomerId) {
        this.$emit('remove', toolId)
      }
    },

    canPut(to, from, item) {
      return !!item.dataset.toolId
    },

    addOne(tool) {
      this.$emit('increase', tool)
    },

    removeOne(tool) {
      this.$emit('decrease', tool)
    }
  }
}
</script>

<style>

</style>
