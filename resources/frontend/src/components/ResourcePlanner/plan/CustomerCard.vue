<template>
  <v-expansion-panel class="customer-card">
    <draggable
      :value="[]"
      :group="{ name: 'resource-planner', put: canPut, pull: false }"
      :data-customer-id="customer.id"
      :disabled="disabled"
      @add="add"
    >
      <v-expansion-panel-header v-slot="{ open }">
        <div>
          <div
            class="d-flex justify-space-between "
          >
            <p class="mr-2 h3 mb-1">
              <span class="font-bold title">{{ customer.lastname }}
                {{ customer.firstname }}{{ resource.rapportdetails.length ? ':': '' }}</span>

              <span
                v-for="(rapportdetail, index) of resource.rapportdetails"
                :key="'r'+rapportdetail.id"
                class="mb-1 teal--text text--darken-2"
              >{{ index !== 0 ? ',' : '' }}
                {{ rapportdetail.employee.lastname }}
                {{ rapportdetail.employee.firstname }}</span><span
                v-for="(car) of resource.cars"
                :key="'c' +car.id"
                class="mb-1 blue--text text--darken-2"
              >, {{ car.name }}</span><span
                v-for="(tool) of resource.tools"
                :key="'t'+tool.id"
                class="mb-1 orange--text text--darken-2"
              >, {{ tool.pivot.amount > 1 ? tool.pivot.amount + 'x' : '' }} {{ tool.name }}
              </span>
            </p>
            <v-tooltip
              v-if="moreEmployeesAsCars"
              top
            >
              <template v-slot:activator="{ on }">
                <v-icon
                  color="red"
                  v-on="on"
                >
                  info
                </v-icon>
              </template>
              <span>{{ $t('Nicht genügend Sitzplätze in den Autos vorhanden') }}</span>
            </v-tooltip>
          </div>
        </div>
      </v-expansion-panel-header>
      <v-expansion-panel-content>
        <v-row>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="updateHoursForAll"
              type="number"
              :label="$t('Stunden für alle anpassen')"
              @keydown.enter="applyHoursForAll"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-select
              v-model="updateProjectForAll"
              :label="$t('Projekt für alle anpassen')"
              :items="customer.projects"
              item-value="id"
              item-text="name"
              @change="applyProjectForAll"
            ></v-select>
          </v-col>
          <v-col
            cols="12"
            lg="7"
            xl="8"
          >
            <p class="ma-0">
              {{ $t('Mitarbeiter') }}
            </p>
            <draggable-rapportdetail-list
              v-model="resource.rapportdetails"
              class="employees draggable-list"
              :customer="customer"
              :selected-employee-ids="selectedEmployeeIds"
              :disabled="disabled"
              @add="addEmployee"
              @remove="removeEmployee"
            ></draggable-rapportdetail-list>
          </v-col>
          <v-col
            cols="12"
            lg="5"
            xl="4"
          >
            <p class="ma-0">
              {{ $t('Autos') }}
            </p>
            <draggable-car-list
              v-model="resource.cars"
              :customer-id="customer.id"
              :used-car-ids="usedCarIds"
              :disabled="disabled"
              class="cars draggable-list"
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
              :disabled="disabled"
              with-pivot
              class="tools draggable-list"
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
              :disabled="disabled"
              :label="$t('Startzeit')"
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
              :label="$t('Endzeit')"
              :disabled="disabled"
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
              :disabled="disabled"
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
import { mapGetters } from 'vuex'
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
    },
    amountOfUsePerTool: {
      type: Object,
      default: () => ({})
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      updateHoursForAll: null,
      updateProjectForAll: null
    }
  },

  computed: {
    ...mapGetters(['activeEmployees', 'tools', 'cars']),

    customer() {
      return this.resource.customer
    },

    debounceUpdate() {
      return this._.debounce((key, value) => {
        this.axios.put(`resources/${this.resource.id}`, {
          [key]: value
        }).catch(() => {
          this.$store.dispatch('error', this.$t('Änderungen konnten nicht gespeichert werden'))
        })
      }, 500)
    },

    moreEmployeesAsCars() {
      const carSeats = this.resource.cars.reduce((seats, car) => seats + car.seats, 0)

      return carSeats < this.resource.rapportdetails.length
    }
  },

  methods: {
    async addEmployee(employeeId) {
      const alreadyExists = this.resource.rapportdetails
        .find(r => r.employee.id === Number(employeeId))
      if (alreadyExists) {
        this.$store.dispatch('alert', { type: 'warning', text: this.$t('Mitarbeiter bereits bei diesem Kunde vorhanden') })
        return
      }

      const employee = this.activeEmployees.find(v => v.id === Number(employeeId))
      const alreayUsed = this.selectedEmployeeIds.includes(Number(employeeId))
      if (alreayUsed && employee && !employee.resource_planner_white_listed) {
        const add = await this.confirmOverfill({
          title: this.$t('Mitarbeiter ist bereits zugeteilt'),
          text: this.$t('Dieser Mitarbeiter ist bereits einem anderen Kunden zugeteilt. Möchtest du ihn bei zwei Kunden haben?')
        })

        if (!add) return
      }

      this.axios.$post(`resources/${this.resource.id}/rapportdetails`, {
        employee_id: employeeId,
        date: this.date
      }).then(({ data }) => {
        this.resource.rapportdetails.push(data)
      }).catch(error => {
        if (error.includes('Employee already exists fot that day and customer')) {
          this.$store.dispatch('alert', { type: 'warning', text: this.$t('Mitarbeiter bereits bei diesem Kunde vorhanden') })
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

    async addCar(carId) {
      const alreadyExists = this.resource.cars.find(c => c.id === Number(carId))
      if (alreadyExists) {
        this.$store.dispatch('alert', { type: 'warning', text: this.$t('Auto bereits bei diesem Kunde vorhanden') })
        return
      }

      const alreayUsed = this.usedCarIds.includes(Number(carId))
      if (alreayUsed) {
        const add = await this.confirmOverfill({
          title: this.$t('Auto ist bereits zugeteilt'),
          text: this.$t('Dieses Auto ist bereits einem anderen Kunden zugeteilt. Möchtest du es bei zwei Kunden haben?')
        })

        if (!add) return
      }

      const car = this.cars.find(c => c.id === Number(carId))
      if (car.important_comment) {
        this.$swal(this.$t('Achtung'), car.important_comment)
      }

      this.axios.$post(`resources/${this.resource.id}/cars/${carId}`).then(({ data }) => {
        this.resource.cars.push(data)
      }).catch(error => {
        if (error.includes('Car already exists fot that day and customer')) {
          this.$store.dispatch('alert', { type: 'warning', text: this.$t('Auto bereits bei diesem Kunde vorhanden') })
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

    async addTool(toolId, amount = 1) {
      const free = await this.isToolFree(Number(toolId), Number(amount))
      if (!free) return

      this.axios.$post(`resources/${this.resource.id}/tools/${toolId}`, {
        amount
      }).then(({ data }) => {
        data.pivot = {
          amount
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
        const alreadyExists = this.resource.tools.find(t => t.id === Number(data.toolId))
        if (alreadyExists) {
          this.increaseTool(alreadyExists)
        } else {
          this.addTool(data.toolId, data.toolAmount)
        }
      } else if (data.employeeId) {
        this.addEmployee(data.employeeId)
      }
    },

    canPut(to, from) {
      return Number(from.el.dataset.customerId) !== this.customer.id
    },

    async increaseTool(tool) {
      const free = await this.isToolFree(tool.id)
      if (free) {
        this.updateTool(tool, tool.pivot.amount + 1)
      }
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
    },

    async confirmOverfill(options) {
      const { value } = await confirmAction({
        ...options,
        confirmButtonText: this.$t('Ja, hinzufügen'),
        cancelButtonText: this.$t('Nein'),
        showCancelButton: true,
        icon: 'warning'
      })

      return value
    },

    async isToolFree(toolId, amount = 1) {
      const tool = this.tools.find(t => t.id === toolId)

      if ((this.amountOfUsePerTool[toolId] || 0) + amount <= tool.amount) return true

      const add = await this.confirmOverfill({
        title: this.$t('Werkzeug aufgebraucht'),
        text: this.$t('Die Anzahl dieses Werkzeugs ist aufgebraucht. Möchtest du es trotzdem hinzufügen?')
      })

      return add
    },

    async applyHoursForAll() {
      try {
        await Promise.all(
          this.resource.rapportdetails.map(async rapportdetail => {
            await this.axios.$patch(`rapportdetails/${rapportdetail.id}`, {
              hours: this.updateHoursForAll
            })
            rapportdetail.hours = this.updateHoursForAll
          })
        )
      } catch (e) {
        this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
      }
      this.updateHoursForAll = null
    },

    async applyProjectForAll() {
      console.log('tst')
      try {
        await Promise.all(
          this.resource.rapportdetails.map(async rapportdetail => {
            await this.axios.$patch(`rapportdetails/${rapportdetail.id}`, {
              project_id: this.updateProjectForAll
            })
            rapportdetail.project_id = this.updateProjectForAll
          })
        )
      } catch (e) {
        console.log(e)
        this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
      }
      this.updateProjectForAll = null
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
  min-height: 125px;
}

.cars {
  min-height: 45px;
  margin-bottom: 10px;
}

.tools {
  min-height: 45px;
}

.draggable-list {
  background-color: white;
  box-shadow: 1px 2px 5px rgba(0, 0, 0, 0.1);
}
</style>

<style lang="scss">
.sortable-ghost {
  display: none;
}
</style>
