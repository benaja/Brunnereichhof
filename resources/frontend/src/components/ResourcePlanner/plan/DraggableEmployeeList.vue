<template>
  <draggable
    :value="value"
    group="resource-planner"
    class="elevation-1"
  >
    <employee-card
      v-for="employee of value"
      :key="employee.id"
      :employee="employee"
      @rightClick="addEmployeeToCustomer(employee.id)"
    ></employee-card>
  </draggable>
</template>

<script>
import EmployeeCard from '@/components/ResourcePlanner/plan/EmployeeCard'
import Draggable from 'vuedraggable'

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
    selectedResource: {
      type: Object,
      default: null
    }
  },
  methods: {
    addEmployeeToCustomer(employeeId) {
      if (this.selectedResource) {
        this.axios.$post(`resources/${this.selectedResource.id}/rapportdetails`, {
          employee_id: employeeId
        }).then(({ data }) => {
          this.selectedResource.rapportdetails.push(data)
        }).catch(error => {
          if (error.includes('Employee already exists fot that day and customer')) {
            this.$store.dispatch('alert', { type: 'warning', text: this.$t('Mitarbeiter bereits bei diesem Kunde vorhanden') })
          } else {
            this.$store.dispatch('error', this.$t('Mitarbeiter konnte nicht zu Kunde hinzugefügt werden'))
          }
        })
      }
    }
  }
}
</script>

<style>

</style>
