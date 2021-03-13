<template>
  <draggable-card
    :data-rapportdetail-id="value.id"
    :data-employee-id="employee.id"
    class="teal lighten-5 mb-2"
    no-flex
  >
    <div class="info-container">
      <employee-card-content :employee="employee"></employee-card-content>
    </div>
    <v-row>
      <v-col
        cols="12"
        lg="4"
      >
        <v-text-field
          v-model="value.hours"
          dense
          :label="$t('Stunden')"
          @input="update('hours')"
        ></v-text-field>
      </v-col>
      <v-col
        cols="12"
        lg="8"
      >
        <v-select
          v-model="value.project_id"
          :label="$t('Projekt')"
          :items="customer.projects"
          item-value="id"
          item-text="name"
          dense
          @input="update('project_id')"
        ></v-select>
      </v-col>
    </v-row>
  </draggable-card>
</template>

<script>
import DraggableCard from './DraggableCard'
import EmployeeCardContent from './EmployeeCardContent'

export default {
  components: {
    DraggableCard,
    EmployeeCardContent
  },
  props: {
    value: {
      type: Object,
      required: true
    },
    customer: {
      type: Object,
      required: true
    }
  },
  computed: {
    employee() {
      return this.value.employee
    },

    update() {
      return this._.debounce(key => {
        this.axios.$patch(`rapportdetails/${this.value.id}`, {
          [key]: this.value[key]
        }).catch(() => {
          this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
        })
      }, 300)
    }
  }
}
</script>

<style lang="scss" scoped>
.info-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
