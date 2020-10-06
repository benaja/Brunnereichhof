<template>
  <div>
    <navigation-bar
      :title="`${employee && employee.isGuest ? 'Gast' : 'Mitarbeiter'} bearbeiten`"
    >
      <template v-if="hasPedingChanges">
        <v-btn
          color="primary"
          class="ml-auto"
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
        save-on-change
        @change="changed"
        @submit="saveChanges"
      ></employee-form>
      <p><strong>Saldo:</strong> {{ employee.saldo }} CHF</p>
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
import EmployeeForm from '@/components/forms/EmployeeForm'

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
    // currently not used, because image cant be restored
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
    }
  }
}
</script>
