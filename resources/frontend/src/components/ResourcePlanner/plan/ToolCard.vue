<template>
  <draggable-card
    :data-tool-id="tool.id"
    :data-tool-amount="tool.pivot && tool.pivot.amount"
    :name="tool.name"
    class="orange lighten-5"
  >
    <div class="d-flex align-center justify-space-between tool-container">
      <p class="mb-0">
        {{ tool.name }}
      </p>
      <div
        v-if="withPivot"
        class="d-flex align-center"
      >
        <v-btn
          v-if="!disabled"
          icon
          color="red"
          @click="$emit('remove', tool)"
        >
          <v-icon>remove</v-icon>
        </v-btn>
        <span>{{ tool.pivot.amount }}</span>
        <v-btn
          v-if="!disabled"
          icon
          color="green"
          @click="$emit('add', tool)"
        >
          <v-icon>add</v-icon>
        </v-btn>
      </div>
    </div>
  </draggable-card>
</template>

<script>
import DraggableCard from './DraggableCard'

export default {
  components: {
    DraggableCard
  },
  props: {
    tool: {
      type: Object,
      required: true
    },
    withPivot: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.tool-container {
  width: 100%;
}
</style>
