<template>
  <fragment>
    <navigation-bar
      :title="$t('Einsatzplaner')"
      full-width
      :loading="isLoading.employees"
    >
      <select-day
        v-model="date"
        class="ml-auto mr-10"
      ></select-day>
    </navigation-bar>
    <v-container>
      <v-row>
        <v-col
          cols="12"
          md="6"
        >
          <v-tabs v-model="selectedTab">
            <v-tab>{{ $t('Mitarbeiter') }}</v-tab>
            <v-tab>{{ $t('Autos') }}</v-tab>
            <v-tab>{{ $t('Werkzeuge') }}</v-tab>
          </v-tabs>
          <v-tabs-items v-model="selectedTab">
            <v-tab-item>
              <resource-planner-filter
                v-model="filteredEmployees"
                :items="activeEmployees"
                :item-search-text="getEmployeeName"
                :is-item-used="isEmployeeUsed"
              ></resource-planner-filter>
              <div class="item-list-scroll-container">
                <draggable-employee-list
                  :value="filteredEmployees"
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
                <draggable-car-list :value="filteredCars"></draggable-car-list>
              </div>
            </v-tab-item>
            <v-tab-item>
              <resource-planner-filter
                v-model="filteredTools"
                :items="tools"
                :is-item-used="isToolUsed"
              ></resource-planner-filter>
              <div class="item-list-scroll-container">
                <draggable-tool-list :value="filteredTools"></draggable-tool-list>
              </div>
            </v-tab-item>
          </v-tabs-items>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <select-customer @input="addCustomer"></select-customer>
          <div class="item-list-scroll-container">
            <customer-card
              v-for="resource of resources"
              :key="resource.id"
              :resource="resource"
              :date="date"
              :selected-employee-ids="allSelectedEmployeeIds"
              :used-car-ids="usedCarIds"
              :available-tools="availableTools"
              @remove="removeResource(resource)"
            ></customer-card>
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
      employeeSearchString: null,
      carSearchString: null,
      toolSearchString: null,
      showUsedEmployees: false,
      showUsedCars: false,
      showUsedTools: false,
      filteredEmployees: [],
      filteredCars: [],
      filteredTools: []
    }
  },
  computed: {
    ...mapGetters(['activeEmployees', 'tools', 'cars', 'customers', 'isLoading']),
    availableEmployees() {
      return this.activeEmployees.filter(e => {
        if (this.employeeSearchString
          && !e.name.toLowerCase().includes(this.employeeSearchString.toLowerCase())) {
          return false
        }
        if (this.allSelectedEmployeeIds.includes(e.id) === !this.showUsedEmployees) {
          return false
        }
        return true
      })
    },
    availableTools() {
      return this.tools.filter(t => (this.amountOfUsePerTool[t.id] || 0) < t.amount)
    },
    availableCars() {
      return this.cars.filter(c => !this.usedCarIds.includes(c.id))
    },
    allSelectedEmployeeIds() {
      return this.resources.flatMap(r => r.rapportdetails)
        .map(r => r.employee.id)
    },
    amountOfUsePerTool() {
      return this.resources.flatMap(r => r.tools)
        .reduce((prev, curr) => {
          prev[curr.id] = prev[curr.id] ? prev[curr.id] + 1 : 1
          return prev
        }, {})
    },
    usedCarIds() {
      return this.resources.flatMap(r => r.cars)
        .map(c => c.id)
    }
  },
  watch: {
    date() {
      this.fetchResources()
    }
  },
  async mounted() {
    await this.fetchResources()
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTools')
    await this.$store.dispatch('fetchCars')
    await this.$store.dispatch('fetchCustomers')
  },
  methods: {
    addCustomer(customerId) {
      if (this.resources.find(r => r.customer.id === customerId)) return

      this.axios.$post('resources', {
        customer_id: customerId,
        date: this.date
      }).then(({ data }) => {
        this.resources.push(data)
      }).catch(() => {
        this.$dispatch('error', this.$t('Kunde konnte nicht hinzugefügt werden'))
      })
    },
    async fetchResources() {
      const { data } = await this.axios.$get('resources', { params: { date: this.date } })
      this.resources = data
      console.log(this.resources)
    },
    removeResource(resource) {
      const index = this.resources.indexOf(resource)
      this.resources.splice(index, 1)
      this.resources = [...this.resources]
    },
    getEmployeeName(customer) {
      return `${customer.lastname} ${customer.firstname}`
    },
    isEmployeeUsed(employee) {
      return this.allSelectedEmployeeIds.includes(employee.id)
    },
    isCarUsed(car) {
      return this.usedCarIds.includes(car.id)
    },
    isToolUsed(tool) {
      return (this.amountOfUsePerTool[tool.id] || 0) >= tool.amount
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
  max-height: calc(100vh - 250px);
}
</style>
