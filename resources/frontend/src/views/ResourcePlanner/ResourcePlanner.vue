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
              <div class="d-flex">
                <v-text-field
                  v-model="employeeSearchString"
                  :label="$t('Suchen')"
                  prepend-icon="search"
                ></v-text-field>
                <v-checkbox
                  v-model="showUsed"
                  :label="$t('Verwendete anzeigen')"
                ></v-checkbox>
              </div>
              <draggable-employee-list
                :value="availableEmployees"
              ></draggable-employee-list>
            </v-tab-item>
            <v-tab-item>
              <draggable-car-list v-model="availableCars"></draggable-car-list>
            </v-tab-item>
            <v-tab-item>
              <draggable-tool-list v-model="availableCars"></draggable-tool-list>
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
            :date="date"
            :selected-employee-ids="allSelectedEmployeeIds"
          ></customer-card>
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

export default {
  components: {
    SelectDay,
    SelectCustomer,
    CustomerCard,
    DraggableCarList,
    DraggableEmployeeList,
    DraggableToolList
  },
  data() {
    return {
      date: this.$moment()
        .add(1, 'days')
        .format('YYYY-MM-DD'),
      newEmployees: [],
      newEmployees2: [],
      availableCars: [],
      availableTools: [],
      selectedTab: 0,
      selectedCustomers: [],
      employeeSearchString: null,
      showUsed: false
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
        if (this.allSelectedEmployeeIds.includes(e.id) === !this.showUsed) {
          return false
        }
        return true
      })
    },
    allSelectedEmployeeIds() {
      return this.selectedCustomers.flatMap(c => c.rapportdetails)
        .map(r => r.employee.id)
    }
  },
  watch: {
    cars() {
      this.availableCars = this.cars
    },
    tools() {
      this.availableTools = this.tools
    }
  },
  async mounted() {
    await this.getDay()
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTools')
    await this.$store.dispatch('fetchCars')
    await this.$store.dispatch('fetchCustomers')
  },
  methods: {
    addCustomer(customerId) {
      if (this.selectedCustomers.find(c => c.id === customerId)) return

      const customer = this.customers.find(c => c.id === customerId)
      this.selectedCustomers.push(this._.cloneDeep(customer))
    },
    async getDay() {
      const { data } = await this.axios.$get(`resource-planner/${this.date}`)
      this.selectedCustomers = data
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
