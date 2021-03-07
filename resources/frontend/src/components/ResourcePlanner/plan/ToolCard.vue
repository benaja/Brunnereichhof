<template>
  <draggable-card
    :data-tool-id="tool.id"
    :name="tool.name"
  >
    <div class="d-flex align-center justify-space-between tool-container">
      <p class="mb-0">
        {{ tool.name }}
      </p>
      <div class="d-flex align-center">
        <v-btn
          icon
          color="red"
          @click="$emit('remove', tool)"
        >
          <v-icon>remove</v-icon>
        </v-btn>
        <span>{{ tool.pivot.amount }}</span>
        <v-btn
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
    }
  }
}
</script>

<style lang="scss" scoped>
.tool-container {
  width: 100%;
}
</style>
