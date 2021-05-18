<template>
  <tr>
    <th>
      {{ employee.lastname }} {{ employee.firstname }}
    </th>
    <td
      v-for="(rapportdetail, index) of rapportdetailsForWeek"
      :key="index"
    >
      <div v-if="rapportdetail">
        <v-text-field
          v-model="rapportdetail.hours"
          label="Stunden"
          type="number"
          :tabindex="rapportdetail.day*1000 + index+1"
          :readonly="!hasPermisstionToChangeRapport"
          min="0"
          dense
          class="mt-3"
          @input="change('hours', rapportdetail)"
          @wheel="event => event.preventDefault()"
        ></v-text-field>
        <v-select
          v-if="settings.rapportFoodTypeEnabled"
          v-model="rapportdetail.foodtype_id"
          :background-color="rapportdetail.foodtype_ok ? '' : 'red'"
          :color="rapportdetail.foodtype_ok ? 'primary' : 'red'"
          label="Verpflegung"
          :items="foodTypes"
          dense
          :readonly="!hasPermisstionToChangeRapport"
          @change="change('foodtype_id', rapportdetail)"
        ></v-select>
        <v-select
          v-model="rapportdetail.project_id"
          label="Projekt"
          :items="projects"
          item-value="id"
          item-text="name"
          dense
          :readonly="!hasPermisstionToChangeRapport"
          @change="change('project_id', rapportdetail)"
        ></v-select>
        <v-select
          v-model="rapportdetail.contract_type"
          label="Vertrag"
          :items="contractTypes"
          :readonly="!hasPermisstionToChangeRapport"
          dense
          @change="change('contract_type', rapportdetail)"
        ></v-select>
      </div>
      <v-btn
        v-else
        color="primary"
        text
        @click="createRapportdetail(index)"
      >
        Hinzufügen
      </v-btn>
    </td>
  </tr>
</template>

<script>
import { contractTypes, foodTypes } from '@/utils'

export default {
  props: {
    rapportdetails: {
      type: Array,
      default: () => ([])
    },
    rapport: {
      type: Object,
      required: true
    },
    settings: {
      type: Object,
      default: null
    },
    projects: {
      type: Array,
      default: null
    }
  },
  data() {
    return {
      contractTypes,
      foodTypes
    }
  },
  computed: {
    employee() {
      return this.rapportdetails[0] && this.rapportdetails[0].employee
    },
    rapportdetailsForWeek() {
      const startdate = this.$moment(this.rapport.startdate, 'YYYY-MM-DD')
      const rapportdetails = []

      for (let i = 0; i < 6; i++) {
        const date = startdate.clone().add('days', i)
        rapportdetails.push(this.rapportdetails.find(r => r.date === date.format('YYYY-MM-DD')))
      }
      return rapportdetails
    },
    hasPermisstionToChangeRapport() {
      return this.$auth.user().hasPermission(['superadmin'], ['rapport_write'])
    }
  },
  methods: {
    change(changedElement, rapportdetail) {
      if (changedElement === 'hours' && rapportdetail.hours < 0) rapportdetail.hours = 0
      this.$store.commit('isSaving', true)
      this.axios
        .patch(`rapportdetails/${rapportdetail.id}`, {
          [changedElement]: rapportdetail[changedElement]
        })
        .then(response => {
          if (changedElement === 'foodtype_id' || changedElement === 'hours') {
            rapportdetail.foodtype_ok = response.data.foodtype_ok
          }
        })
        .catch(() => {
          this.$swal('Fehler beim speicher', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        }).finally(() => {
          this.$store.commit('isSaving', false)
        })
    },
    createRapportdetail(day) {
      const date = this.$moment(this.rapport.startdate, 'YYYY-MM-DD').add(day, 'days')

      this.axios.$post('rapportdetails', {
        rapport_id: this.rapport.id,
        project_id: this.rapport.default_project_id,
        employee_id: this.employee.id,
        day,
        contract_type: this.contractTypes[0].value,
        date: date.format('YYYY-MM-DD')
      }).then(data => {
        this.rapportdetails.push(data)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
  th {
    text-align: left;
    vertical-align: top;
    min-width: 200px;
    padding-top: 20px;
  }

  td {
    padding: 5px 10px;
    text-align: center;
  }
</style>
