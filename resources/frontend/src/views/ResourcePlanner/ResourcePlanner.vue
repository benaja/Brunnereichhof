<template>
  <fragment>
    <navigation-bar
      :title="$t('Einsatzplaner')"
      full-width
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
                group="employee"
              >
                <employee-card
                  v-for="employee of availableEmployees"
                  :key="employee.id"
                  :employee="employee"
                ></employee-card>
              </draggable>
            </v-tab-item>
          </v-tabs-items>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <draggable
            :list="newEmployees"
            class="new-employees"
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
import { mapGetters } from 'vuex'
import Draggable from 'vuedraggable'


export default {
  components: {
    SelectDay,
    Draggable,
    EmployeeCard
  },
  data() {
    return {
      date: this.$moment()
        .add(1, 'days')
        .format('YYYY-MM-DD'),
      newEmployees: [],
      newEmployees2: [],
      availableEmployees: [],
      selectedTab: 0
    }
  },
  computed: {
    ...mapGetters(['employees', 'tools', 'cars'])
  },
  watch: {
    employees() {
      this.availableEmployees = this.employees
    }
  },
  async mounted() {
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTools')
    await this.$store.dispatch('fetchCars')
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
