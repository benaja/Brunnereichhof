<template>
  <draggable
    :value="internalValue"
    :data-customer-id="customerId"
    group="cars"
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

      const alreadyExists = this.value.find(v => v.id === Number(carId))
      if (alreadyExists) {
        this.$store.dispatch('alert', { type: 'warning', text: this.$t('Auto bereits vorhanden') })
        return
      }

      const alreayUsed = this.selectedEmployeeIds.includes(Number(employeeId))
      if (alreayUsed && employee && !employee.resource_planner_white_listed) {
        confirmAction({
          title: this.$t('Mitarbeiter ist bereits zugeteilt'),
          text: this.$t('Dieser Mitarbeiter ist bereits einem anderen Kunden zugeteilt. Möchtest du ihn bei zwei Kunden haben?'),
          confirmButtonText: this.$t('Ja, hinzufügen'),
          cancelButtonText: this.$t('Nein'),
          showCancelButton: true,
          icon: 'warning'
        }).then(result => {
          if (result.value) {
            this.$emit('add', employeeId)
          }
        })
      } else {
        this.$emit('add', employeeId)
      }
    },
    remove(value) {
      const toCustomerId = value.to.dataset.customerId
      const { rapportdetailId } = value.item.dataset

      if (!toCustomerId) {
        this.$emit('remove', rapportdetailId)
      }
    }
  }
}
</script>

<style>

</style>
