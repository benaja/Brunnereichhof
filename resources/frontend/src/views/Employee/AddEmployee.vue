<template>
  <fragment>
    <navigation-bar title="Mitarbeiter erstellen"></navigation-bar>
    <v-container>
      <employee-form
        ref="form"
        v-model="employee"
        @submit="save"
      ></employee-form>
      <v-btn
        :loading="isLoading"
        depressed
        color="primary"
        @click="save"
      >
        Speichern
        <v-icon right>
          send
        </v-icon>
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import EmployeeForm from '@/components/forms/EmployeeForm'

export default {
  components: {
    EmployeeForm
  },
  data() {
    return {
      employee: {
        sex: 'man',
        isGuest: !!this.$route.query.guest,
        user: {},
        isActive: true
      },
      filename: '',
      isLoading: false
    }
  },
  computed: {
    allValid() {
      if (!this.employee.firstname) return false
      if (!this.employee.lastname) return false
      return true
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        const formData = new FormData()
        if (this.employee.profileimage) {
          formData.append('profileimage', this.employee.profileimage)
        }
        formData.append('data', JSON.stringify(this.employee))
        this.axios
          .post('employees', formData)
          .then(() => {
            this.redirect()
          })
          .catch(error => {
            if (error.includes('validation.unique')) {
              this.$swal('Email existiert bereits', 'Es existiert bereits ein anderer User mit der selben Email', 'error')
            } else {
              this.$swal('Fehler beim Speichern', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
            }
          }).finally(() => {
            this.isLoading = false
          })
      } else {
        this.$store.dispatch('alert', { text: 'Bitte fülle alle Felder korrekt aus', type: 'error' })
      }
    },
    profileImageChanged() {
      if (this.$refs.profileImage.files.length === 1) {
        const file = this.$refs.profileImage.files[0]
        this.filename = file.name
      } else {
        this.filename = ''
      }
    },
    redirect() {
      if (this.employee.isGuest) {
        this.$router.push('/guests')
      } else {
        this.$router.push('/employee')
      }
    }
  }
}
</script>
