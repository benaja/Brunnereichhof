<template>
  <draggable-card
    :data-employee-id="employee.id"
    :data-rapportdetail-id="value.id"
    :name="`${employee.lastname} ${employee.firstname}`"
    :image="employee.small_profile_image"
  ></draggable-card>
</template>

<script>
import DraggableCard from './DraggableCard'

export default {
  components: {
    DraggableCard
  },
  props: {
    value: {
      type: Object,
      required: true
    }
  },
  computed: {
    employee() {
      return this.value.employee || this.value
    }
  }
}
</script>

<style>

</style>
