<template>
  <div>
    <navigation-bar title="Hofmitarbeiter erstellen">
    </navigation-bar>
    <v-form>
      <v-container>
        <worker-form
          ref="form"
          v-model="worker"
          @submit="save"
        ></worker-form>
        <p>
          Nach dem Erstellen des Hofmitarbeiters erhält er
          automatisch eine E-Mail mit seinem Passwort.
        </p>
        <v-btn
          color="primary"
          class="ml-auto"
          depressed
          :loading="isLoading"
          @click="save"
        >
          Speichern
        </v-btn>
      </v-container>
    </v-form>
  </div>
</template>

<script>
import WorkerForm from '@/components/worker/WorkerForm'

export default {
  components: {
    WorkerForm
  },
  data() {
    return {
      worker: {
        isActive: true
      },
      isLoading: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        this.axios
          .post('workers', this.worker)
          .then(() => {
            this.$router.push('/worker')
          })
          .catch(error => {
            if (error.response.data.errors.email && error.response.data.errors.email.includes('validation.unique')) {
              this.$swal('Email existiert bereits', 'Diese Email wurde bereits verwendet', 'error')
            } else {
              this.$swal('Fehler beim Speichern', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
            }
          }).finally(() => {
            this.isLoading = false
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  background-color: white;
  border-radius: 5px;
}

.save_button {
  text-align: center;
  margin-bottom: 30px;
}
</style>
