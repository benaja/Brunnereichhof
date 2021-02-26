<template>
  <v-card
    :elevation="1"
    class="customer-card pa-3"
  >
    <div
      class="d-flex justify-space-between"
    >
      <h3>{{ customer.lastname }} {{ customer.firstname }}</h3>
      <v-btn icon>
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
          v-model="customer.rapportdetails"
          class="employees"
          :customer-id="customer.id"
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
          v-model="cars"
          class="cars"
        ></draggable-car-list>
        <p class="ma-0">
          {{ $t('Werkzeuge') }}
        </p>
        <draggable-tool-list
          v-model="tools"
          class="tools"
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
    customer: {
      type: Object,
      default: null
    },
    date: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      cars: [],
      tools: []
    }
  },
  computed: {
    employees: {
      get() {
        return this.customer.rapportdetails || []
      },
      set() {

      }
    }
  },
  methods: {
    addEmployee(employeeId) {
      this.axios.$post(`customers/${this.customer.id}/rapportdetails`, {
        employee_id: employeeId,
        date: this.date
      }).then(({ data }) => {
        if (this.customer.rapportdetails) {
          this.customer.rapportdetails.push(data)
        } else {
          this.$set(this.customer, 'rapportdetails', [data])
        }
      }).catch(error => {
        if (error.includes('Employee already exists fot that day and customer')) {
          this.$store.dispatch('alert', { type: 'warning', text: this.$t('Mitarbeiter bereits vorhanden') })
        } else {
          this.$store.dispatch('error', this.$t('Mitarbeiter konnte nicht zu Kunde hinzugefügt werden'))
        }
      })
    },
    removeEmployee(rapportdetailId) {
      this.axios.$delete(`customers/${this.customer.id}/rapportdetails/${rapportdetailId}`).then(() => {
        const rapportdetail = this.customer.rapportdetails.find(r => r.id === Number(rapportdetailId))
        const index = this.customer.rapportdetails.indexOf(rapportdetail)
        this.customer.rapportdetails.splice(index, 1)
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Mitarbeiter konnte nicht von Kunde entfernt werden'))
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
