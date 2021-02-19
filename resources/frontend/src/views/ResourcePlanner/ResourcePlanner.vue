<template>
  <fragment>
    <navigation-bar
      :title="$t('Einsatzplaner')"
      full-width
      :loading="isLoading.employees"
    ></navigation-bar>
    <v-container>
      <select-day v-model="date"></select-day>
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
              <draggable
                :list="availableEmployees"
                group="employees"
              >
                <employee-card
                  v-for="employee of availableEmployees"
                  :key="employee.id"
                  :employee="employee"
                ></employee-card>
              </draggable>
            </v-tab-item>
            <v-tab-item>
              <draggable
                :list="availableCars"
                group="cars"
              >
                <car-card
                  v-for="car of availableCars"
                  :key="car.id"
                  :car="car"
                ></car-card>
              </draggable>
            </v-tab-item>
            <v-tab-item>
              <draggable
                :list="availableTools"
                group="tools"
              >
                <tool-card
                  v-for="tool of availableTools"
                  :key="tool.id"
                  :tool="tool"
                ></tool-card>
              </draggable>
            </v-tab-item>
          </v-tabs-items>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <select-customer @input="addCustomer"></select-customer>

          <customer-card
            v-for="customer of selectedCustomers"
            :key="customer.id"
            :customer="customer"
          ></customer-card>


          <draggable
            :list="selectedCustomers"
            class="customer-list"
            group="employee"
          >
            <employee-card
              v-for="employee of newEmployees"
              :key="employee.id"
              :employee="employee"
            ></employee-card>
          </draggable>

          <draggable
            :list="newEmployees2"
            class="new-employees"
            group="employee"
          >
            <p
              v-for="employee of newEmployees2"
              :key="employee.id"
            >
              {{ employee.name }}
            </p>
          </draggable>
        </v-col>
      </v-row>
    </v-container>
  </fragment>
</template>

<script>
import SelectDay from '@/components/ResourcePlanner/SelectDay'
import EmployeeCard from '@/components/ResourcePlanner/plan/EmployeeCard'
import CarCard from '@/components/ResourcePlanner/plan/CarCard'
import ToolCard from '@/components/ResourcePlanner/plan/ToolCard'
import SelectCustomer from '@/components/ResourcePlanner/plan/SelectCustomer'
import CustomerCard from '@/components/ResourcePlanner/plan/CustomerCard'
import { mapGetters } from 'vuex'
import Draggable from 'vuedraggable'


export default {
  components: {
    SelectDay,
    Draggable,
    EmployeeCard,
    CarCard,
    ToolCard,
    SelectCustomer,
    CustomerCard
  },
  data() {
    return {
      date: this.$moment()
        .add(1, 'days')
        .format('YYYY-MM-DD'),
      newEmployees: [],
      newEmployees2: [],
      availableEmployees: [],
      availableCars: [],
      availableTools: [],
      selectedTab: 0,
      selectedCustomers: []
    }
  },
  computed: {
    ...mapGetters(['employees', 'tools', 'cars', 'customers', 'isLoading'])
  },
  watch: {
    employees() {
      this.availableEmployees = this.employees
    },
    cars() {
      this.availableCars = this.cars
    },
    tools() {
      this.availableTools = this.tools
    }
  },
  async mounted() {
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTools')
    await this.$store.dispatch('fetchCars')
    await this.$store.dispatch('fetchCustomers')
  },
  methods: {
    addCustomer(customerId) {
      const customer = this.customers.find(c => c.id === customerId)
      this.selectedCustomers.push(this._.cloneDeep(customer))
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

</style>
