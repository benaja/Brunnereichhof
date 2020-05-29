<template>
  <v-row justify="center">
    <v-dialog
      v-model="isOpen"
      max-width="600px"
      :persistent="!rapport.default_project_id"
    >
      <v-card max-width="600px">
        <v-card-title>
          <h3>Standard Projekt auswählen</h3>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text v-if="customer">
          <v-select
            v-model="rapport.default_project_id"
            label="Projekt"
            :items="customer.projects"
            item-value="id"
            item-text="name"
            @change="save"
          ></v-select>
          <h3 class="mt-4">
            Projekte von Kunde bearbeiten
          </h3>
          <edit-projects
            v-model="customer.projects"
            :customer-id="rapport.customer_id"
            @updateProjects="updatedProjects => $emit('updatedProjects', updatedProjects)"
          ></edit-projects>
          <v-row justify="center">
            <v-btn
              color="primary"
              :disabled="!rapport.default_project_id"
              depressed
              @click="$emit('input', false)"
            >
              Fertig
            </v-btn>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import EditProjects from '@/components/customer/EditProjects'

export default {
  components: {
    EditProjects
  },
  props: {
    value: Boolean,
    projects: {
      type: Array,
      default: null
    },
    rapport: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      selectedProject: null,
      isOpen: false,
      customer: null
    }
  },
  watch: {
    value() {
      this.isOpen = this.value
    },
    isOpen() {
      this.$emit('input', this.isOpen)
    }
  },
  mounted() {
    this.isOpen = this.value
    this.axios.get(`customers/${this.rapport.customer_id}`).then(response => {
      this.customer = response.data
    })
  },
  methods: {
    save() {
      this.$store.commit('isSaving', true)
      this.axios
        .patch(`/rapports/${this.$route.params.id}`, {
          default_project_id: this.rapport.default_project_id
        })
        .then(response => {
          this.rapport.default_project_id = response.data.default_project_id
        })
        .catch(() => {
          this.$swal('Fehler', 'Standard Projekt konnte nicht gesetzt werden', 'error')
        }).finally(() => {
          this.$store.commit('isSaving', false)
        })
    }
  }
}
</script>
