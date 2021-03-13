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
      <template v-if="resources.length">
        <v-btn
          v-if="!isCompleted"
          color="primary"
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
          outlined
          @click="finish(false)"
        >
          <v-icon class="mr-2">
            edit
          </v-icon>
          {{ $t('Bearbeiten') }}
        </v-btn>
      </template>
    </navigation-bar>
    <v-container
      fluid
      class="pb-0"
    >
      <v-row no-gutters>
        <v-col
          v-if="!isCompleted"
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
                v-model="filteredEmployees"
                :items="activeEmployees"
                :item-search-text="getEmployeeName"
                :is-item-used="isEmployeeUsed"
              ></resource-planner-filter>
              <div class="item-list-scroll-container">
                <draggable-employee-list
                  :value="filteredEmployees"
                  class="list-min-height"
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
                ></draggable-tool-list>
              </div>
            </v-tab-item>
          </v-tabs-items>
        </v-col>
        <v-col
          cols="12"
          :md="isCompleted ? 12 : 6"
          :lg="isCompleted ? 12 : 7"
          :xl="isCompleted ? 12 : 8"
        >
          <select-customer
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
              multiple
              flat
            >
              <customer-card
                v-for="resource of filteredResources"
                :key="resource.id"
                :resource="resource"
                :date="date"
                :selected-employee-ids="allSelectedEmployeeIds"
                :used-car-ids="usedCarIds"
                :available-tools="availableTools"
                :amount-of-use-per-tool="amountOfUsePerTool"
                @remove="removeResource(resource)"
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
import { confirmAction } from '@/utils'

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
      plannerDay: null
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
      return this.plannerDay && this.plannerDay.completed
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
      }).then(() => {
        this.fetchResources()
      }).catch(() => {
        this.$dispatch('error', this.$t('Kunde konnte nicht hinzugefügt werden'))
      })
    },
    async fetchResources() {
      const { data } = await this.axios.$get('resources', { params: { date: this.date } })
      this.resources = data.resources
      this.plannerDay = data
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
</style>
