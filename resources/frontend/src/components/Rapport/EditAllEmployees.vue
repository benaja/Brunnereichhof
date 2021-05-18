<template>
  <td>
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
  </td>
</template>

<script>
import { contractTypes, foodTypes } from '@/utils'

export default {
  props: {
    rapport: {
      type: Object,
      required: true
    },
    rapportdetails: {
      type: Array,
      default: () => []
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
      defaultProject: this.rapport.default_project_id,
      defaultFoodType: 1,
      defaultContractType: this.rapportdetails.length > 0 ? this.rapportdetails[0].contract_type : 'work_contract',
      contractTypes,
      foodTypes
    }
  },
  computed: {
    hasPermisstionToChangeRapport() {
      return this.$auth.user().hasPermission(['superadmin'], ['rapport_write'])
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
    }
  },
  methods: {
    rapportdetailsForDay() {

    }
  }
}
</script>

<style lang="scss" scoped>
td {
  padding: 5px 10px;
  text-align: center;
}
</style>
