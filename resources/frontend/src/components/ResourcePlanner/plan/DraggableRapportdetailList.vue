<template>
  <draggable
    :value="internalValue"
    group="employees"
    class="elevation-1"
    :data-customer-id="customerId"
    @add="add"
    @remove="remove"
  >
    <rapportdetail-card
      v-for="rapportdetail of internalValue"
      :key="rapportdetail.id"
      :value="rapportdetail"
    ></rapportdetail-card>
  </draggable>
</template>

<script>
import RapportdetailCard from '@/components/ResourcePlanner/plan/RapportdetailCard'
import Draggable from 'vuedraggable'
import { confirmAction } from '@/utils'
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
    customerId: {
      type: Number,
      default: null
    },
    selectedEmployeeIds: {
      type: Array,
      default: () => []
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

      const alreadyExists = this.value.find(v => v.employee.id === Number(employeeId))
      if (alreadyExists) {
        this.$store.dispatch('alert', { type: 'warning', text: this.$t('Mitarbeiter bereits vorhanden') })
        return
      }

      const employee = this.activeEmployees.find(v => v.id === Number(employeeId))
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
