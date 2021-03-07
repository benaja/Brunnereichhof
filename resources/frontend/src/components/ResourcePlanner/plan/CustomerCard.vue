<template>
  <v-expansion-panel class="customer-card">
    <draggable
      :value="[]"
      :group="{ name: 'resource-planner', put: canPut, pull: false }"
      @add="add"
    >
      <v-expansion-panel-header v-slot="{ open }">
        <div>
          <div
            class="d-flex justify-space-between "
          >
            <div class="d-flex flex-wrap">
              <p class="mr-2 h3">
                <span class="font-bold title">{{ customer.lastname }}
                  {{ customer.firstname }}{{ resource.rapportdetails.length ? ':': '' }}</span>

                <span
                  v-for="(rapportdetail, index) of resource.rapportdetails"
                  :key="rapportdetail.id"
                  class="mb-1"
                >{{ index !== 0 ? ',' : '' }}
                  {{ rapportdetail.employee.lastname }}
                  {{ rapportdetail.employee.firstname }}</span><span
                  v-for="(car) of resource.cars"
                  :key="car.id"
                  class="mb-1"
                >, {{ car.name }}</span><span
                  v-for="(tool) of resource.tools"
                  :key="tool.id"
                  class="mb-1"
                >, {{ tool.name }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </v-expansion-panel-header>
      <v-expansion-panel-content>
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
              with-pivot
              class="tools"
              @add="addTool"
              @remove="removeTool"
              @decrease="decreaseTool"
              @increase="increaseTool"
            ></draggable-tool-list>
          </v-col>
        </v-row>
        <v-row no-gutters>
          <v-col
            cols="12"
            md="6"
            class="pr-md-2"
          >
            <time-text-field
              v-model="resource.start_time"
              label="Startzeit"
              @input="debounceUpdate('start_time', $event)"
            ></time-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
            class="pl-md-2"
          >
            <time-text-field
              v-model="resource.end_time"
              label="Endzeit"
              @input="debounceUpdate('end_time', $event)"
            ></time-text-field>
          </v-col>
          <v-col
            cols="12"
          >
            <v-textarea
              v-model="resource.comment"
              auto-grow
              :rows="1"
              label="Kommentar"
              @input="debounceUpdate('comment', $event)"
            ></v-textarea>
          </v-col>
        </v-row>
        <div class="text-right">
          <v-btn
            color="red"
            icon
            @click="removeResource"
          >
            <v-icon>delete</v-icon>
          </v-btn>
        </div>
      </v-expansion-panel-content>
    </draggable>
  </v-expansion-panel>
</template>

<script>
import Draggable from 'vuedraggable'
import TimeTextField from '@/components/general/TimeTextField'
import { confirmAction } from '@/utils'
import DraggableRapportdetailList from './DraggableRapportdetailList'
import DraggableCarList from './DraggableCarList'
import DraggableToolList from './DraggableToolList'

export default {
  components: {
    DraggableCarList,
    DraggableToolList,
    DraggableRapportdetailList,
    Draggable,
    TimeTextField
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
    },

    debounceUpdate() {
      return this._.debounce((key, value) => {
        this.axios.put(`resources/${this.resource.id}`, {
          [key]: value
        }).catch(() => {
          this.$store.dispatch('error', this.$t('Änderungen konnten nicht gespeichert werden'))
        })
      }, 500)
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
      confirmAction(this.$t('Willst du {kunde} wirklich von der aktuellen Planung entfernen?', {
        kunde: `${this.customer.lastname} ${this.customer.firstname}`
      })).then(value => {
        if (value) {
          this.axios.$delete(`resources/${this.resource.id}`).then(() => {
            this.$emit('remove')
          }).catch(() => {
            this.$store.dispatch('error', this.$t('Kunde konnte nicht entfernt werden'))
          })
        }
      })
    },
    addTool(toolId) {
      this.axios.$post(`resources/${this.resource.id}/tools/${toolId}`).then(({ data }) => {
        data.pivot = {
          amount: 1
        }
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
    },
    add(event) {
      const data = event.item.dataset
      if (data.carId) {
        this.addCar(data.carId)
      } else if (data.toolId) {
        this.addTool(data.toolId)
      } else if (data.employeeId) {
        this.addEmployee(data.employeeId)
      }
    },
    canPut(to, from) {
      return Number(from.el.dataset.customerId) !== this.customer.id
    },
    increaseTool(tool) {
      this.updateTool(tool, tool.pivot.amount + 1)
    },
    decreaseTool(tool) {
      if (tool.pivot.amount === 1) {
        this.removeTool(tool.id)
      } else {
        this.updateTool(tool, tool.pivot.amount - 1)
      }
    },
    updateTool(tool, amount) {
      this.axios.$patch(`resources/${this.resource.id}/tools/${tool.id}`, {
        amount
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
      }).then(() => {
        tool.pivot.amount = amount
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.customer-card {
  background-color: rgb(248, 248, 248) !important;
  margin-bottom: 10px;
}

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

.test-container {
  background-color: lightgray;
  min-height: 50px;
}
</style>

<style lang="scss">
.sortable-ghost {
  display: none;
}
</style>
