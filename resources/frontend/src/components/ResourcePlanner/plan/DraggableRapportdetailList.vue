<template>
  <draggable
    :value="internalValue"
    :group="{name: 'resource-planner', pull: 'clone', put: canPut}"
    :data-customer-id="customer.id"
    :disabled="disabled"
    @add="add"
    @remove="remove"
  >
    <rapportdetail-card
      v-for="rapportdetail of internalValue"
      :key="rapportdetail.id"
      :customer="customer"
      :value="rapportdetail"
      :disabled="disabled"
      @rightClick="$emit('remove', rapportdetail.id)"
    ></rapportdetail-card>
  </draggable>
</template>

<script>
import RapportdetailCard from '@/components/ResourcePlanner/plan/RapportdetailCard'
import Draggable from 'vuedraggable'
import { mapGetters } from 'vuex'

export default {
  components: {
    RapportdetailCard,
    Draggable
  },
  props: {
    value: {
      type: Array,
      default: () => []
    },
    customer: {
      type: Object,
      required: true
    },
    selectedEmployeeIds: {
      type: Array,
      default: () => []
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      internalValue: this.value,
      fromCustomerId: null,
      toCustomerId: null
    }
  },
  computed: {
    ...mapGetters(['activeEmployees'])
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
      const { employeeId } = value.item.dataset

      this.$emit('add', employeeId)
    },
    remove(value) {
      const toCustomerId = value.to.dataset.customerId
      const { rapportdetailId } = value.item.dataset

      if (!toCustomerId) {
        this.$emit('remove', rapportdetailId)
      }
    },

    canPut(to, from, item) {
      return !!item.dataset.employeeId
    }
  }
}
</script>

<style>

</style>
