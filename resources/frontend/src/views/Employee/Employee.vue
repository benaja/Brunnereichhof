<template>
  <div>
    <navigation-bar
      :title="`${employee && employee.isGuest ? 'Gast' : 'Mitarbeiter'} bearbeiten`"
    >
      <template v-if="hasPedingChanges">
        <v-btn
          color="red"
          class="my-2 ml-auto mr-2"
          outlined
          :loading="isLoadingRevert"
          @click="revertChanges"
        >
          Abbrechen
        </v-btn>
        <v-btn
          color="primary"
          class="my-2"
          depressed
          :loading="isLoadingSave"
          @click="saveChanges"
        >
          Fertig
        </v-btn>
      </template>
    </navigation-bar>
    <v-container>
      <progress-linear :loading="isLoading"></progress-linear>
      <employee-form
        ref="form"
        v-model="employee"
        :original="original"
        :readonly="!isUserAllowedToEdit"
        :loading-image="isUploadingImage"
        :loading-delete-image="isDeleteingImage"
        @change="changed"
        @submit="saveChanges"
        @uploadImage="uploadImage"
        @deleteImage="deleteImage"
      ></employee-form>
      <v-btn
        v-if="isUserAllowedToEdit"
        color="red white--text my-4"
        depressed
        :loading="isLoadingDelete"
        @click="deleteEmployee"
      >
        <v-icon class="mr-2">
          delete
        </v-icon>
        Löschen
      </v-btn>
    </v-container>
  </div>
</template>

<script>
import { rules } from '@/utils'
import EmployeeForm from '@/components/employee/EmployeeForm'

export default {
  components: {
    EmployeeForm
  },
  data() {
    return {
      employee: {
        user: {}
      },
      original: {
        user: {}
      },
      apiUrl: `employees/${this.$route.params.id}`,
      backendUrl: process.env.VUE_APP_URL,
      rules,
      isLoading: false,
      isLoadingSave: false,
      isLoadingRevert: false,
      isLoadingDelete: false,
      isUploadingImage: false,
      isDeleteingImage: false
    }
  },
  computed: {
    isUserAllowedToEdit() {
      if (this.employee.isGuest) {
        return this.$auth.user().hasPermission(['superadmin'], ['employee_write', 'roomdispositioner_write'])
      }
      return this.$auth.user().hasPermission(['superadmin'], ['employee_write'])
    },
    hasPedingChanges() {
      return !this._.isEqual(this.employee, this.original)
    }
  },
  mounted() {
    this.isLoading = true
    this.axios.get(this.apiUrl).then(response => {
      this.employee = response.data
      this.original = this._.cloneDeep(this.employee)
    }).catch(() => {
      this.$store.dispatch('error', 'Mitarbeiter konnte nicht geladen werden')
    }).finally(() => {
      this.isLoading = false
    })
  },
  methods: {
    changed() {
      if (this.$refs.form.validate()) {
        this.$store.commit('isSaving', true)
        this.axios.put(this.apiUrl, this.employee).catch(error => {
          if (error.includes('Email already exist')) {
            this.$store.dispatch('alert', { text: 'Email existiert bereits', type: 'error', duration: 6 })
          } else {
            this.$store.dispatch('alert', { text: 'Fehler beim Speichern', type: 'error' })
          }
        }).finally(() => {
          this.$store.commit('isSaving', false)
        })
      }
    },
    updateEmployee() {
      return new Promise((resolve, reject) => {
        this.axios.put(this.apiUrl, this.employee).catch(error => {
          if (error.includes('Email already exist')) {
            this.$store.dispatch('alert', { text: 'Email existiert bereits', type: 'error', duration: 6 })
          } else {
            this.$store.dispatch('alert', { text: 'Änderungen konnten nicht gespeichert werden', type: 'error' })
          }
          reject(error)
        }).then(response => resolve(response))
      })
    },
    saveChanges() {
      if (this.$refs.form.validate() && this.hasPedingChanges) {
        this.isLoadingSave = true
        this.updateEmployee().then(() => {
          this.original = this._.cloneDeep(this.employee)
          this.$store.dispatch('alert', { text: 'Änderungen erfolgreich gespeichert' })
        }).finally(() => {
          this.isLoadingSave = false
        })
      }
    },
    revertChanges() {
      this.isLoadingRevert = true
      this.axios.put(this.apiUrl, this.original).then(() => {
        this.employee = this._.cloneDeep(this.original)
        this.$store.dispatch('alert', { text: 'Änderungen erfolgreich zurückgesetzt' })
      }).catch(() => {
        this.$store.dispatch('error', 'Änderungen konnten nicht rückgängig gemacht werden')
      }).finally(() => {
        this.isLoadingRevert = false
      })
    },
    deleteEmployee() {
      this.isLoadingDelete = true
      this.axios.delete(this.apiUrl).then(() => {
        if (this.employee.isGuest) this.$router.push('/guests')
        else this.$router.push('/employee')
      }).catch(() => {
        this.isLoadingDelete = false
        this.$store.dispatch('error', 'Mitarbeiter konnte nicht gelöscht werden')
      })
    },
    uploadImage(files) {
      if (files.length === 1) {
        this.isUploadingImage = true
        const data = new FormData()
        data.append('profileimage', files[0])
        this.axios
          .post(`${this.apiUrl}/editimage`, data)
          .then(response => {
            this.employee.profileimage = response.data
          })
          .catch(() => {
            this.$store.dispatch('error', 'Bild konnte nicht hochgeladen werden')
          }).finally(() => {
            this.isUploadingImage = false
          })
      }
    },
    deleteImage() {
      this.isDeleteingImage = true
      this.axios
        .delete(`${this.apiUrl}/editimage`)
        .then(() => {
          this.employee.profileimage = null
        })
        .catch(() => {
          this.$store.dispatch('error', 'Bild konnte nicht gelöscht werden')
        }).finally(() => {
          this.isDeleteingImage = false
        })
    }
  }
}
</script>

<style lang="scss" scoped>
.single-employee {
  background-color: white;
  margin-top: 50px;
  border-radius: 5px;
  padding: 20px;
  box-shadow: 0 0 10px lightgray;
}

input {
  border-bottom: none;
}

.description {
  margin-top: 12px;
}

.delete_button {
  text-align: center;
}

.new-image {
  text-align: center;
  margin-top: 100px;
}

.profileimage {
  max-width: 100%;
  max-height: 240px;
  position: relative;
  display: block;
  margin: 0 auto 10px auto;
}

.image-buttons {
  text-align: center;

  a {
    margin-left: 10px;
    margin-right: 10px;
  }
}

.hidden {
  display: none;
}

@media only screen and (max-width: 600px) {
  .container {
    padding: 0;
  }

  .single-employee {
    margin-top: 0;
    background-color: transparent;
    box-shadow: none;
  }
}
</style>
