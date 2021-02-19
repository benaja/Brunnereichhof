<template>
  <v-card
    :elevation="1"
    class="customer-card pa-3"
  >
    <div
      class="d-flex justify-space-between"
    >
      <h3>{{ customer.name }}</h3>
      <v-btn icon>
        <v-icon>delete</v-icon>
      </v-btn>
    </div>
    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <draggable
          :list="employees"
          class="employees"
          group="employees"
        >
          <employee-card
            v-for="employee of employees"
            :key="employee.id"
            :employee="employee"
          ></employee-card>
        </draggable>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <draggable
          :list="cars"
          class="cars"
          group="cars"
        >
          <car-card
            v-for="car of cars"
            :key="car.id"
            :car="car"
          ></car-card>
        </draggable>
        <draggable
          :list="tools"
          class="tools"
          group="tools"
        >
          <tool-card
            v-for="tool of tools"
            :key="tool.id"
            :tool="tool"
          ></tool-card>
        </draggable>
      </v-col>
    </v-row>
  </v-card>
</template>

<script>
import Draggable from 'vuedraggable'
import EmployeeCard from '@/components/ResourcePlanner/plan/EmployeeCard'
import CarCard from '@/components/ResourcePlanner/plan/CarCard'
import ToolCard from '@/components/ResourcePlanner/plan/ToolCard'

export default {
  components: {
    Draggable,
    EmployeeCard,
    CarCard,
    ToolCard
  },
  props: {
    customer: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      employees: [],
      cars: [],
      tools: []
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
