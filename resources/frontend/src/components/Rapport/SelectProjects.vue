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
        <v-card-text>
          <v-select
            v-model="rapport.default_project_id"
            label="Projekt"
            :items="projects"
            item-value="id"
            item-text="name"
            @change="save"
          ></v-select>
          <h3 class="mt-4">
            Projekte von Kunde bearbeiten
          </h3>
          <projects
            :customer-id="rapport.customer_id"
            @updateProjects="updatedProjects => $emit('updatedProjects', updatedProjects)"
          ></projects>
          <v-row justify="center">
            <v-btn
              color="primary"
              :disabled="!rapport.default_project_id"
              @click="$emit('input', false)"
            >
              Speichern
            </v-btn>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import Projects from '@/components/customer/EditProjects'

export default {
  name: 'SelectProjects',
  components: {
    Projects
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
      isOpen: false
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
  },
  methods: {
    save() {
      this.axios
        .patch(`/rapport/${this.$route.params.id}`, {
          default_project_id: this.rapport.default_project_id
        })
        .then(response => {
          this.rapport.default_project_id = response.data.default_project_id
        })
        .catch(() => {
          this.$swal('Fehler', 'Standard Projekt konnte nicht gesetzt werden', 'error')
        })
    }
  }
}
</script>
