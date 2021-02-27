<template>
  <v-card
    :elevation="1"
    class="customer-card pa-3"
  >
    <div
      class="d-flex justify-space-between"
    >
      <h3>{{ customer.lastname }} {{ customer.firstname }}</h3>
      <v-btn
        icon
        @click="removeResource"
      >
        <v-icon>delete</v-icon>
      </v-btn>
    </div>
    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <p class="ma-0">
          {{ $t('Mitarbeiter') }}
        </p>
        <draggable-rapportdetail-list
          v-model="resource.rapportdetails"
          class="employees"
          :customer-id="customer.id"
          :selected-employee-ids="selectedEmployeeIds"
          @add="addEmployee"
          @remove="removeEmployee"
        ></draggable-rapportdetail-list>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <p class="ma-0">
          {{ $t('Autos') }}
        </p>
        <draggable-car-list
          v-model="resource.cars"
          :customer-id="customer.id"
          :used-car-ids="usedCarIds"
          class="cars"
          @add="addCar"
          @remove="removeCar"
        ></draggable-car-list>
        <p class="ma-0">
          {{ $t('Werkzeuge') }}
        </p>
        <draggable-tool-list
          v-model="resource.tools"
          :customer-id="customer.id"
          :available-tools="availableTools"
          class="tools"
          @add="addTool"
          @remove="removeTool"
        ></draggable-tool-list>
      </v-col>
    </v-row>
  </v-card>
</template>

<script>
import DraggableRapportdetailList from './DraggableRapportdetailList'
import DraggableCarList from './DraggableCarList'
import DraggableToolList from './DraggableToolList'

export default {
  components: {
    DraggableCarList,
    DraggableToolList,
    DraggableRapportdetailList
  },
  props: {
    resource: {
      type: Object,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    selectedEmployeeIds: {
      type: Array,
      default: () => []
    },
    usedCarIds: {
      type: Array,
      default: () => []
    },
    availableTools: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    customer() {
      return this.resource.customer
    },
    employees: {
      get() {
        return this.resource.rapportdetails || []
      },
      set() {

      }
    }
  },
  methods: {
    addEmployee(employeeId) {
      this.axios.$post(`resources/${this.resource.id}/rapportdetails`, {
        employee_id: employeeId,
        date: this.date
      }).then(({ data }) => {
        this.resource.rapportdetails.push(data)
      }).catch(error => {
        if (error.includes('Employee already exists fot that day and customer')) {
          this.$store.dispatch('alert', { type: 'warning', text: this.$t('Mitarbeiter bereits vorhanden') })
        } else {
          this.$store.dispatch('error', this.$t('Mitarbeiter konnte nicht zu Kunde hinzugefügt werden'))
        }
      })
    },
    removeEmployee(rapportdetailId) {
      this.axios.$delete(`rapportdetails/${rapportdetailId}`).then(() => {
        const rapportdetail = this.resource.rapportdetails
          .find(r => r.id === Number(rapportdetailId))
        const index = this.resource.rapportdetails.indexOf(rapportdetail)
        this.resource.rapportdetails.splice(index, 1)
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Mitarbeiter konnte nicht von Kunde entfernt werden'))
      })
    },
    addCar(carId) {
      this.axios.$post(`resources/${this.resource.id}/cars/${carId}`).then(({ data }) => {
        this.resource.cars.push(data)
      }).catch(error => {
        if (error.includes('Car already exists fot that day and customer')) {
          this.$store.dispatch('alert', { type: 'warning', text: this.$t('Auto bereits vorhanden') })
        } else {
          this.$store.dispatch('error', this.$t('Auto konnte nicht zu Kunde hinzugefügt werden'))
        }
      })
    },
    removeCar(carId) {
      this.axios.$delete(`resources/${this.resource.id}/cars/${carId}`).then(() => {
        const car = this.resource.cars
          .find(c => c.id === Number(carId))
        const index = this.resource.cars.indexOf(car)
        this.resource.cars.splice(index, 1)
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Auto konnte nicht von Kunde entfernt werden'))
      })
    },
    removeResource() {
      this.axios.$delete(`resources/${this.resource.id}`).then(() => {
        this.$emit('remove')
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Kunde konnte nicht entfernt werden'))
      })
    },
    addTool(toolId) {
      this.axios.$post(`resources/${this.resource.id}/tools/${toolId}`).then(({ data }) => {
        this.resource.tools.push(data)
      }).catch(error => {
        if (error.includes('Car already exists fot that day and customer')) {
          this.$store.dispatch('alert', { type: 'warning', text: this.$t('Werkzeug bereits vorhanden') })
        } else {
          this.$store.dispatch('error', this.$t('Werkzeug konnte nicht zu Kunde hinzugefügt werden'))
        }
      })
    },
    removeTool(toolId) {
      this.axios.$delete(`resources/${this.resource.id}/tools/${toolId}`).then(() => {
        const car = this.resource.tools
          .find(c => c.id === Number(toolId))
        const index = this.resource.tools.indexOf(car)
        this.resource.tools.splice(index, 1)
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Werkzeug konnte nicht von Kunde entfernt werden'))
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.employees {
  background-color: rgb(232, 232, 232);
  min-height: 110px;
}

.cars {
  min-height: 50px;
  margin-bottom: 10px;
  background-color: rgb(232, 232, 232);
}

.tools {
  min-height: 50px;
  background-color: rgb(232, 232, 232);
}
</style>
