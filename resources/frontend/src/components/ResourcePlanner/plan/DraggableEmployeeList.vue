<template>
  <draggable
    :value="internalValue"
    group="employees"
    class="elevation-1"
    :data-customer-id="customerId"
    @change="change"
    @end="dragEnd"
  >
    <employee-card
      v-for="employee of internalValue"
      :key="employee.id"
      :value="employee"
    ></employee-card>
  </draggable>
</template>

<script>
import EmployeeCard from '@/components/ResourcePlanner/plan/EmployeeCard'
import Draggable from 'vuedraggable'
import { confirmAction } from '@/utils'

export default {
  components: {
    EmployeeCard,
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
  },
  methods: {
    change(event) {
      if (event.added) {
        this.$emit('add', event.added.element)
      }
      if (event.removed) {
        this.$emit('remove', event.removed.element)
      }
    },
    dragEnd(value) {
      if (value.from.dataset.customerId && value.to.dataset.customerId) {
        confirmAction({
          title: this.$t('Verschieben oder Duplizieren'),
          text: this.$t('Willst du den Mitarbeiter verschieben oder duplizieren?'),
          confirmButtonText: this.$t('Verschieben'),
          cancelButtonText: this.$t('Duplizieren'),
          showCancelButton: true,
          icon: 'warning'
        })
      }
    }
  }
}
</script>

<style>

</style>
