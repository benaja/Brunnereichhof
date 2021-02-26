<template>
  <draggable-card
    :data-tool-id="tool.id"
    :name="tool.name"
  ></draggable-card>
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

<style>

</style>
