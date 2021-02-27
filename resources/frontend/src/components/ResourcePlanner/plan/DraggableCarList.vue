<template>
  <draggable
    :value="internalValue"
    :data-customer-id="customerId"
    :group="{name: 'cars', pull: 'clone'}"
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
import { confirmAction } from '@/utils'

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
    },
    usedCarIds: {
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
    add(value) {
      const { carId } = value.item.dataset

      const alreadyExists = this.value.find(v => v.id === Number(carId))
      if (alreadyExists && this.customerId) {
        this.$store.dispatch('alert', { type: 'warning', text: this.$t('Auto bereits vorhanden') })
        return
      }

      const alreayUsed = this.usedCarIds.includes(Number(carId))
      if (alreayUsed) {
        confirmAction({
          title: this.$t('Auto ist bereits zugeteilt'),
          text: this.$t('Dieses Auto ist bereits einem anderen Kunden zugeteilt. Möchtest du es bei zwei Kunden haben?'),
          confirmButtonText: this.$t('Ja, hinzufügen'),
          cancelButtonText: this.$t('Nein'),
          showCancelButton: true,
          icon: 'warning'
        }).then(result => {
          if (result.value) {
            this.$emit('add', carId)
          }
        })
      } else {
        this.$emit('add', carId)
      }
    },
    remove(value) {
      const toCustomerId = value.to.dataset.customerId
      const { carId } = value.item.dataset

      if (!toCustomerId) {
        this.$emit('remove', carId)
      }
    }
  }
}
</script>

<style>

</style>
