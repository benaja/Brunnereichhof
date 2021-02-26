<template>
  <draggable
    :value="internalValue"
    group="employees"
    class="elevation-1"
    :data-customer-id="customerId"
    @change="change"
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
      internalValue: this.value,
      fromCustomerId: null,
      toCustomerId: null
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
    dragAdd(value) {
      console.log(value)
    },
    change(event) {
      if (event.added) {
        this.$emit('add', event.added.element)
      }
      if (event.removed) {
        this.$emit('remove', event.removed.element)
      }
    },
    dragEnd(value) {
      const fromCustomerId = value.from.dataset.customerId
      const toCustomerId = value.to.dataset.customerId
      const { employeeId } = value.item.dataset


      // if (fromCustomerId && toCustomerId) {
      //   confirmAction({
      //     title: this.$t('Verschieben oder Duplizieren'),
      //     text: this.$t('Willst du den Mitarbeiter verschieben oder duplizieren?'),
      //     confirmButtonText: this.$t('Verschieben'),
      //     cancelButtonText: this.$t('Duplizieren'),
      //     showCancelButton: true,
      //     icon: 'warning'
      //   }).then(result => {
      //     if (result.dismiss === 'cancel') {

      //     }
      //   })
      // }
      // if (!fromCustomerId && toCustomerId) {
      //   // console.log(value)
      //   // this.$emit('')
      // }
    }
  }
}
</script>

<style>

</style>
