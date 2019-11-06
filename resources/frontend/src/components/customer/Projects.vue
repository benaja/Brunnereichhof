<template>
  <v-combobox
    v-model="chips"
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
      <v-chip :input-value="data.selected" close @update:active="remove(data.item)">
        <strong>{{ data.item.name }}</strong>&nbsp;
      </v-chip>
    </template>
  </v-combobox>
</template>

<script>
export default {
  name: 'home',
  props: {
    customerId: {
      required: true
    },
    readonly: {
      default: false
    }
  },
  data() {
    return {
      apiUrl: process.env.VUE_APP_API_URL + 'project',
      chips: [],
      items: [],
      areItemsLoaded: false
    }
  },
  mounted() {
    this.axios.get(`/customer/${this.customerId}/projects`).then(response => {
      this.chips = response.data
    })
    this.axios.get(this.apiUrl).then(response => {
      this.items = response.data
    })
  },
  methods: {
    chipAdded() {
      let addedProjectname = this.chipsInstance.chipsData[this.chipsInstance.chipsData.length - 1].tag
      this.axios.get(this.apiUrl + '/exist/' + addedProjectname).then(response => {
        if (response.data === 1) {
          this.axios
            .post(this.apiUrl + '/add', {
              title: addedProjectname,
              customerId: this.customerId
            })
            .catch(() => {
              console.error('error while adding project to customer')
            })
        }
      })
    },
    remove(item) {
      if (!this.readonly) {
        if (item.name !== 'Allgemein') {
          this.chips.splice(this.chips.indexOf(item), 1)
          this.chips = [...this.chips]
          this.removeProject(item)
        } else {
          this.$swal('Das Projekt "Allgemein" kann nicht entfernt werden.')
        }
      }
    },
    removeProject(item) {
      if (item.id) {
        this.axios
          .delete(this.apiUrl + '/' + item.id + '/customer/' + this.customerId)
          .catch(() => {
            console.error('could no delete')
          })
          .then(() => {
            this.$emit('updateProjects', this.chips)
          })
      }
    },
    addProject(item) {
      if (item.id) {
        this.axios
          .post(this.apiUrl + '/add', {
            projectId: item.id,
            customerId: this.customerId
          })
          .then(() => {
            this.$emit('updateProjects', this.chips)
          })
          .catch(() => {
            console.error('error while adding project to customer')
          })
      } else if (this.items.find(i => i.name.toLowerCase() === item.toLowerCase())) {
        this.remove(item)
      } else {
        this.createProject(item)
      }
    },
    createProject(title) {
      this.$swal({
        title: '"' + title + '" existiert noch nicht!',
        text: 'Wollen sie ein neues Projekt erstellen',
        confirmButtonText: 'Ja',
        cancelButtonText: 'Nein',
        showCancelButton: true
      }).then(async result => {
        if (result.value) {
          let description = await this.$swal({
            title: 'Geben sie doch eine Beschreibung ein!',
            input: 'text',
            inputPlaceholder: 'Beschreibung',
            confirmButtonText: 'Erstellen',
            allowOutsideClick: false
          })
          this.axios
            .post(this.apiUrl, {
              title: title,
              description: description.value,
              customerId: this.customerId
            })
            .then(response => {
              let newProject = {
                name: title,
                id: response.data
              }
              this.chips.push(newProject)
              this.items.push(newProject)
              this.$emit('updateProjects', this.chips)
            })
            .catch(() => {
              this.$swal('Erstellung fehlgeschlagen!', 'Etwas ist schief gelaufen!', 'error')
            })
        }
        this.remove(title)
      })
    }
  },
  watch: {
    chips(chips, oldChips) {
      if (!this.areItemsLoaded) {
        this.areItemsLoaded = true
        return
      }
      if (chips.length < oldChips.length) {
        for (let chip of oldChips) {
          let index = chips.indexOf(chip)
          if (index < 0) {
            if (chip.name !== 'Allgemein') {
              this.removeProject(chip)
              return
            } else {
              this.chips.push(chip)
            }
          }
        }
      } else if (chips.length > oldChips.length) {
        for (let chip of chips) {
          let index = oldChips.indexOf(chip)
          if (index < 0) {
            this.addProject(chip)
            return
          }
        }
      }
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
