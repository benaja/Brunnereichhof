<template>
  <div class="rapport-day">
    <p class="font-weight-bold my-3 pl-1">
      {{ date.format('dddd') }}
    </p>
    <v-divider></v-divider>
    <edit-field
      v-model="rapport[currentCommentString]"
      class="mt-1"
      placeholder="Bemerkung"
      :readonly="!hasPermisstionToChangeRapport"
      @input="updateComment"
    ></edit-field>
    <div
      class="mx-1 all-days"
      :class="{ 'small-height': !settings.rapportFoodTypeEnabled }"
    >
      <p class="font-weight-bold d-md-none">
        Alle
      </p>
      <v-select
        v-if="settings.rapportFoodTypeEnabled"
        v-model="defaultFoodType"
        label="Verpflegung"
        :items="foodTypes"
        color="black"
        dense
        class="mt-3"
        :readonly="!hasPermisstionToChangeRapport"
      ></v-select>
      <v-select
        v-model="defaultProject"
        label="Projekt"
        :items="projects"
        item-value="id"
        item-text="name"
        dense
        :class="{'mt-3': !settings.rapportFoodTypeEnabled}"
        :readonly="!hasPermisstionToChangeRapport"
      ></v-select>
      <v-select
        v-model="defaultContractType"
        label="Vertrag"
        :items="contractTypes"
        dense
        :readonly="!hasPermisstionToChangeRapport"
      ></v-select>
    </div>
    <div
      v-for="(rapportdetail, index) of rapportdetails"
      :key="'rapportdetails-' + rapportdetail.id"
      class="mx-1 mt-4 rapportday"
      :class="{ 'small-height': !settings.rapportFoodTypeEnabled }"
    >
      <p
        v-if="rapportdetail.employee"
        class="font-weight-bold d-md-none"
      >
        {{ rapportdetail.employee.lastname }} {{ rapportdetail.employee.firstname }}
      </p>
      <v-text-field
        v-model="rapportdetail.hours"
        label="Stunden"
        type="number"
        :tabindex="day*1000 + index+1"
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
    <p class="pl-1 mt-4">
      <span class="d-md-none font-weight-bold">Total:</span>
      {{ totalHours }}
    </p>
  </div>
</template>


<script>
import _ from 'lodash'

export default {
  props: {
    date: {
      type: Object,
      default: null
    },
    employees: {
      type: Array,
      default: null
    },
    rapport: {
      type: Object,
      default: null
    },
    day: {
      type: Number,
      default: null
    },
    projects: {
      type: Array,
      default: null
    },
    rapportdetails: {
      type: Array,
      default: null
    },
    settings: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      defaultProject: this.rapport.default_project_id,
      defaultFoodType: 1,
      defaultContractType: this.rapportdetails.length > 0 ? this.rapportdetails[0].contract_type : 'work_contract',
      contractTypes: [
        {
          value: 'work_contract',
          text: 'Werksvertrag'
        },
        {
          value: 'staff_grant',
          text: 'Personalverleih'
        }
      ],
      foodTypes: [
        {
          value: 1,
          text: 'Eichhof'
        },
        {
          value: 2,
          text: 'Kunde'
        },
        {
          value: 3,
          text: 'Keine Angabe'
        }
      ],
      hasPermisstionToChangeRapport: false
    }
  },
  computed: {
    currentCommentString() {
      if (this.day === 0) {
        return 'comment_mo'
      } if (this.day === 1) {
        return 'comment_tu'
      } if (this.day === 2) {
        return 'comment_we'
      } if (this.day === 3) {
        return 'comment_th'
      } if (this.day === 4) {
        return 'comment_fr'
      } if (this.day === 5) {
        return 'comment_sa'
      }
      return null
    },
    totalHours() {
      return this.rapportdetails
        .reduce((total, rapportdetail) => Number(total) + Number(rapportdetail.hours), 0)
    }
  },
  watch: {
    defaultContractType() {
      for (const rapportdetail of this.rapportdetails) {
        rapportdetail.contract_type = this.defaultContractType
      }
      this.$store.commit('isSaving', true)
      this.axios
        .patch('/rapportdetails', {
          rapportdetails: this.rapportdetails
        })
        .catch(() => {
          this.$swal('Fehler', 'Das Projekt konnte nicht geändert werden', 'error')
        }).finally(() => {
          this.$store.commit('isSaving', false)
        })
    },
    defaultProject() {
      for (const rapportdetail of this.rapportdetails) {
        rapportdetail.project_id = this.defaultProject
      }
      this.$store.commit('isSaving', true)
      this.axios
        .patch('/rapportdetails', {
          rapportdetails: this.rapportdetails
        })
        .then(() => {
          this.$store.commit('isSaving', false)
        })
        .catch(() => {
          this.$store.commit('isSaving', false)
          this.$swal('Fehler', 'Das Projekt konnte nicht geändert werden', 'error')
        })
    },
    defaultFoodType() {
      for (const rapportdetail of this.rapportdetails) {
        rapportdetail.foodtype_id = this.defaultFoodType
      }
      this.$store.commit('isSaving', true)
      this.axios
        .patch('/rapportdetails', {
          rapportdetails: this.rapportdetails
        })
        .then(response => {
          this.$store.commit('isSaving', false)
          for (let i = 0; i < this.rapportdetails.length; i++) {
            this.rapportdetails[i].foodtype_ok = response.data[i].foodtype_ok
          }
        })
        .catch(() => {
          this.$swal('Fehler', 'Die Verpflegung konnte nicht geändert werden', 'error')
          this.$store.commit('isSaving', false)
        })
    },
    'rapport.rapportdetails': function() {
      this.addEmployeeToRapportdetails()
    }
  },
  mounted() {
    this.addEmployeeToRapportdetails()
    this.hasPermisstionToChangeRapport = this.$auth.user().hasPermission(['superadmin'], ['rapport_write'])
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
    updateComment: _.debounce(function() {
      this.$store.commit('isSaving', true)
      this.axios
        .patch(`rapports/${this.rapport.id}`, {
          [this.currentCommentString]: this.rapport[this.currentCommentString]
        })
        .catch(() => {
          this.$swal('Fehler beim speicher', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        }).finally(() => {
          this.$store.commit('isSaving', false)
        })
    }, 400),
    addEmployeeToRapportdetails() {
      for (const rapportdetail of this.rapportdetails) {
        rapportdetail.employee = this.employees.find(e => e.id === rapportdetail.employee_id)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.rapport-day {
  width: 100%;
}
@media only screen and (min-width: 600px) {
  .all-days {
    height: 160px;
    overflow: hidden;

    &.small-height {
      height: 100px;
    }
  }

  .rapportday {
    height: 220px;
    overflow: hidden;

    &.small-height {
      height: 170px;
    }
  }
}
</style>
