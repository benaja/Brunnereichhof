<template>
  <v-combobox
    v-model="selectedProjects"
    :items="items"
    item-value="id"
    item-text="name"
    label="Projekte"
    chips
    prepend-icon="filter_list"
    multiple
    :readonly="readonly"
  >
    <template v-slot:selection="data">
      <v-chip
        :input-value="data.selected"
        close
        @update:active="remove(data.item)"
      >
        <strong>{{ data.item.name }}</strong>&nbsp;
      </v-chip>
    </template>
  </v-combobox>
</template>

<script>
export default {
  props: {
    customerId: {
      type: [Number, String],
      default: null
    },
    readonly: {
      type: Boolean,
      default: false
    },
    value: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      items: [],
      areItemsLoaded: false
    }
  },
  computed: {
    selectedProjects: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    }
  },
  watch: {
    value(projects, oldProjects) {
      console.log('watch value', projects)
      if (!this.areItemsLoaded) {
        this.areItemsLoaded = true
        return
      }
      if (this.areItemsLoaded) {
        const addedProjects = this._.difference(projects, oldProjects)
        const removedProjects = this._.difference(oldProjects, projects)
        if (addedProjects.length === 1) {
          this.addProject(addedProjects[0])
        } else if (removedProjects.length === 1) {
          if (removedProjects[0].name === 'Allgemein') {
            this.selectedProjects.push(removedProjects[0])
          } else {
            this.removeProject(removedProjects[0])
          }
        }
      }
    }
  },
  mounted() {
    this.axios.get('projects').then(response => {
      this.items = response.data
    })
  },
  methods: {
    remove(project) {
      if (!this.readonly) {
        if (project.name !== 'Allgemein') {
          // make a copy on the selectedProjects to delete it
          // this is done to detect the removed project in the watch function
          const projects = [...this.selectedProjects]
          projects.splice(projects.indexOf(project), 1)
          this.selectedProjects = projects
        } else {
          this.$swal('Das Projekt "Allgemein" kann nicht entfernt werden.')
        }
      }
    },
    removeProject(project) {
      if (project.id && this.customerId) {
        this.$store.commit('isSaving', true)
        this.axios
          .delete(`customers/${this.customerId}/projects/${project.id}`)
          .catch(() => {
            this.$store.dispatch('error', 'Projekt konnte nicht entfernt werden')
          }).finally(() => {
            this.$store.commit('isSaving', false)
          })
      }
    },
    addProject(project) {
      if (typeof project === 'string') {
        const projectAlreadySelected = this.selectedProjects.find(p => p.name
          && p.name.toLowerCase() === project.toLowerCase())
        const projectExist = this.items.find(p => p.name
          && p.name.toLowerCase() === project.toLowerCase())
        this.remove(project)

        // wait for next tick so that the 'value' property is updated after call this.remove()
        this.$nextTick(() => {
          if (projectExist && !projectAlreadySelected) {
            // make a clone to detect the added project in the watch function
            const projects = [...this.selectedProjects]
            projects.push(projectExist)
            this.selectedProjects = projects
          } else if (!projectAlreadySelected) {
            this.createProject(project)
          }
        })
      }
      if (project.id && this.customerId) {
        this.$store.commit('isSaving', true)
        this.axios
          .post(`customers/${this.customerId}/projects/${project.id}`).catch(() => {
            this.$store.dispatch('error', 'Projekt konnte nicht hinzugefügt werden')
          }).finally(() => {
            this.$store.commit('isSaving', false)
          })
      }
    },
    createProject(title) {
      this.$swal({
        title: `"${title}" existiert noch nicht!`,
        text: 'Wollen sie ein neues Projekt erstellen',
        confirmButtonText: 'Ja',
        cancelButtonText: 'Nein',
        showCancelButton: true
      }).then(async result => {
        if (result.value) {
          const description = await this.$swal({
            title: 'Geben sie doch eine Beschreibung ein!',
            input: 'text',
            inputPlaceholder: 'Beschreibung',
            confirmButtonText: 'Erstellen',
            allowOutsideClick: false
          })
          this.$store.commit('isSaving', true)
          this.axios
            .post('projects', {
              title,
              description: description.value
            })
            .then(response => {
              // make a clone to detect the added project in the watch function
              const projects = [...this.selectedProjects]
              projects.push({
                name: title,
                id: response.data.id
              })
              this.selectedProjects = projects
              this.items.push(response.data)
            })
            .catch(() => {
              this.$swal('Erstellung fehlgeschlagen!', 'Etwas ist schief gelaufen!', 'error')
            }).finally(() => {
              this.$store.commit('isSaving', false)
            })
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

<style lang="scss">
.swal2-input {
  border: none !important;

  &:focus {
    border-bottom: none !important;
    box-shadow: none !important;
  }
}
</style>
