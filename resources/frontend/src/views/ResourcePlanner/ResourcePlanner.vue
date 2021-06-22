<template>
  <fragment>
    <navigation-bar
      :title="$t('Einsatzplaner')"
      full-width
      :loading="isLoading.employees"
    >
      <v-btn
        depressed
        color="primary"
        class="ml-auto"
        :loading="isLoadingPdf"
        @click="downloadPdf"
      >
        {{ $t('Pdf generieren') }}
      </v-btn>
      <template v-if="resources.length && isAllowedToEdit">
        <v-btn
          v-if="!isCompleted"
          color="primary"
          class="mx-2"
          depressed
          @click="finish(true)"
        >
          <v-icon class="mr-2">
            check
          </v-icon>
          {{ $t('Abschliessen') }}
        </v-btn>
        <v-btn
          v-else
          color="primary"
          class="mx-2"
          outlined
          @click="finish(false)"
        >
          <v-icon class="mr-2">
            edit
          </v-icon>
          {{ $t('Bearbeiten') }}
        </v-btn>
      </template>
      <v-btn
        v-if="!isToday"
        class="mx-2"
        text
        @click="setDateToToday"
      >
        {{ $t('Heute') }}
      </v-btn>
      <select-day
        v-model="date"
        class="mr-5"
      ></select-day>
    </navigation-bar>
    <v-container
      fluid
      class="pb-0"
    >
      <v-row no-gutters>
        <v-col
          v-if="!isCompleted && isAllowedToEdit"
          cols="12"
          md="6"
          lg="5"
          xl="4"
          class="pr-5"
        >
          <v-tabs v-model="selectedTab">
            <v-tab>{{ $t('Mitarbeiter') }}</v-tab>
            <v-tab>{{ $t('Autos') }}</v-tab>
            <v-tab>{{ $t('Werkzeuge') }}</v-tab>
          </v-tabs>
          <v-tabs-items v-model="selectedTab">
            <v-tab-item>
              <resource-planner-filter
                ref="employeeSearch"
                v-model="filteredEmployees"
                :items="activeEmployees"
                :item-search-text="getEmployeeName"
                :is-item-used="isEmployeeUsed"
              ></resource-planner-filter>
              <div class="item-list-scroll-container">
                <draggable-employee-list
                  :value="filteredEmployees"
                  class="list-min-height"
                  :selected-resource="selectedResource"
                  @add="addEmployeeToSelectedResource"
                ></draggable-employee-list>
              </div>
            </v-tab-item>
            <v-tab-item>
              <resource-planner-filter
                v-model="filteredCars"
                :items="cars"
                :is-item-used="isCarUsed"
              ></resource-planner-filter>
              <div class="item-list-scroll-container">
                <draggable-car-list
                  class="list-min-height"
                  :value="filteredCars"
                  @add="addCardToSelectedResource"
                ></draggable-car-list>
              </div>
            </v-tab-item>
            <v-tab-item>
              <resource-planner-filter
                v-model="filteredTools"
                :items="tools"
                :is-item-used="isToolUsed"
              ></resource-planner-filter>
              <div class="item-list-scroll-container">
                <draggable-tool-list
                  :value="filteredTools"
                  class="list-min-height"
                  @add="addToolToSelectedResource"
                ></draggable-tool-list>
              </div>
            </v-tab-item>
          </v-tabs-items>
        </v-col>
        <v-col
          cols="12"
          :md="isCompleted || !isAllowedToEdit ? 12 : 6"
          :lg="isCompleted || !isAllowedToEdit ? 12 : 7"
          :xl="isCompleted || !isAllowedToEdit ? 12 : 8"
        >
          <select-customer
            v-if="!isCompleted && isAllowedToEdit"
            :selected-customers="selectedCustomers"
            @input="addCustomer"
          ></select-customer>
          <v-text-field
            v-model="customerSearchString"
            class="mt-2"
            :label="$t('Kunde suchen')"
            prepend-icon="search"
          ></v-text-field>
          <div class="item-list-scroll-container">
            <v-expansion-panels
              v-model="openCustomer"
              flat
            >
              <customer-card
                v-for="resource of filteredResources"
                ref="customerCards"
                :key="resource.id"
                :resource="resource"
                :date="date"
                :selected-employee-ids="allSelectedEmployeeIds"
                :used-car-ids="usedCarIds"
                :available-tools="availableTools"
                :amount-of-use-per-tool="amountOfUsePerTool"
                :disabled="isCompleted || !isAllowedToEdit"
                @remove="removeResource(resource)"
                @employeeAdded="employeeAdded"
              ></customer-card>
            </v-expansion-panels>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </fragment>
</template>

<script>
import SelectDay from '@/components/ResourcePlanner/SelectDay'
import SelectCustomer from '@/components/ResourcePlanner/plan/SelectCustomer'
import CustomerCard from '@/components/ResourcePlanner/plan/CustomerCard'
import { mapGetters } from 'vuex'
import DraggableCarList from '@/components/ResourcePlanner/plan/DraggableCarList'
import DraggableEmployeeList from '@/components/ResourcePlanner/plan/DraggableEmployeeList'
import DraggableToolList from '@/components/ResourcePlanner/plan/DraggableToolList'
import ResourcePlannerFilter from '@/components/ResourcePlanner/plan/ResourcePlannerFilter'
import { confirmAction, downloadFile } from '@/utils'

export default {
  components: {
    SelectDay,
    SelectCustomer,
    CustomerCard,
    DraggableCarList,
    DraggableEmployeeList,
    DraggableToolList,
    ResourcePlannerFilter
  },
  data() {
    return {
      date: this.$moment()
        .add(1, 'days')
        .format('YYYY-MM-DD'),
      newEmployees: [],
      newEmployees2: [],
      selectedTab: 0,
      resources: [],
      customerSearchString: null,
      filteredEmployees: [],
      filteredCars: [],
      filteredTools: [],
      plannerDay: null,
      isLoadingPdf: false,
      openCustomer: null
    }
  },
  computed: {
    ...mapGetters(['activeEmployees', 'tools', 'cars', 'customers', 'isLoading']),
    availableTools() {
      return this.tools.filter(t => (this.amountOfUsePerTool[t.id] || 0) < t.amount)
    },
    allSelectedEmployeeIds() {
      return this.resources.flatMap(r => r.rapportdetails)
        .map(r => r.employee.id)
    },
    amountOfUsePerTool() {
      return this.resources.flatMap(r => r.tools)
        .reduce((prev, curr) => {
          prev[curr.id] = prev[curr.id] ? prev[curr.id] + curr.pivot.amount : curr.pivot.amount
          return prev
        }, {})
    },
    usedCarIds() {
      return this.resources.flatMap(r => r.cars)
        .map(c => c.id)
    },
    selectedCustomers() {
      return this.resources.map(r => r.customer)
    },
    filteredResources() {
      return this.resources.filter(r => {
        const customerName = `${r.customer.lastname} ${r.customer.firstname}`.toLowerCase()
        return !this.customerSearchString
          || customerName.toLowerCase().includes(this.customerSearchString.toLowerCase())
      })
    },
    isCompleted() {
      return !!(this.plannerDay && this.plannerDay.completed)
    },
    isAllowedToEdit() {
      return this.$auth.user().hasPermission(['superadmin'], ['resource_planner_write'])
    },
    isToday() {
      return this.$moment(this.date).isSame(this.$moment(), 'day')
    },
    selectedResource() {
      if (this.openCustomer === null) return null

      return this.filteredResources[this.openCustomer]
    }
  },
  watch: {
    date() {
      this.fetchResources()
    }
  },
  async mounted() {
    await this.fetchResources()

    if (this.isAllowedToEdit) {
      await this.$store.dispatch('fetchEmployees')
      await this.$store.dispatch('fetchTools')
      await this.$store.dispatch('fetchCars')
    }
  },
  methods: {
    addCustomer(customerId) {
      if (this.resources.find(r => r.customer.id === customerId)) return

      this.axios.$post(`planner-day/${this.plannerDay.id}/customers`, {
        customer_id: customerId
      }).then(() => {
        this.fetchResources()
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Kunde konnte nicht hinzugefügt werden'))
      })
    },
    async fetchResources() {
      const { data } = await this.axios.$get('resources', { params: { date: this.date } })
      this.resources = data.resources
      this.plannerDay = data

      if (this.isAllowedToEdit) {
        await this.$store.dispatch('fetchCustomers', {
          withHourrecords: this.date
        })
      }
    },
    removeResource(resource) {
      const index = this.resources.indexOf(resource)
      this.resources.splice(index, 1)
      this.resources = [...this.resources]
    },
    getEmployeeName(customer) {
      return `${customer.lastname} ${customer.firstname} ${customer.callname || ''}`
    },
    isEmployeeUsed(employee) {
      return this.allSelectedEmployeeIds.includes(employee.id)
    },
    isCarUsed(car) {
      return this.usedCarIds.includes(car.id)
    },
    isToolUsed(tool) {
      return (this.amountOfUsePerTool[tool.id] || 0) >= tool.amount
    },
    finish(yes) {
      confirmAction(
        yes
          ? this.$t('Willst du die Planung für diesen Tag wirklich abschliessen?')
          : this.$t('Willst du die Planung für diesen Tag wirklich wieder bearbeiten?'),
        yes ? this.$t('Ja, Abschliessen') : this.$t('Ja, Bearbeiten')
      ).then(value => {
        if (value) {
          this.axios.$patch(`planner-day/${this.plannerDay.id}`, {
            history_enabled: this.plannerDay.history_enabled || yes,
            completed: yes
          }).catch(() => {
            this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
          }).then(() => {
            this.plannerDay.history_enabled = this.plannerDay.history_enabled || yes
            this.plannerDay.completed = yes
          })
        }
      })
    },
    downloadPdf() {
      this.isLoadingPdf = true
      downloadFile(`pdf/planner-day/${this.plannerDay.id}`).catch(() => {
        this.$store.dispatch('error', this.$t('Pdf konnte nicht erstellt werden'))
      }).finally(() => {
        this.isLoadingPdf = false
      })
    },
    setDateToToday() {
      this.date = this.$moment().format('YYYY-MM-DD')
    },
    addEmployeeToSelectedResource(employeeId) {
      if (this.selectedResource) {
        this.$refs.customerCards[this.openCustomer].addEmployee(employeeId)
      }
    },
    addCardToSelectedResource(carId) {
      if (this.selectedResource) {
        this.$refs.customerCards[this.openCustomer].addCar(carId)
      }
    },
    addToolToSelectedResource(toolId) {
      if (this.selectedResource) {
        const alreadyExists = this.selectedResource.tools.find(t => t.id === toolId)
        if (alreadyExists) {
          this.$refs.customerCards[this.openCustomer].increaseTool(alreadyExists)
        } else {
          this.$refs.customerCards[this.openCustomer].addTool(toolId)
        }
      }
    },
    employeeAdded() {
      if (this.$refs.employeeSearch) {
        this.$refs.employeeSearch.reset()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.content-container {
  display: flex;
}

.content-list {
  width: 100%;
}

.new-employees {
  background-color: red;
  height: 100px;
  margin-top: 200px;
}

.item-list-scroll-container {
  overflow-y: auto;
  max-height: calc(100vh - 220px);
}

.list-min-height {
  min-height: 400px;
}

.today-placeholder {
  width: 100px;
}
</style>
