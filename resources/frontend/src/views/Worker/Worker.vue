<template>
  <fragment>
    <navigation-bar title="Hofmitarbeiter bearbeiten">
      <template v-if="hasChanges">
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
    <v-container
      v-if="worker"
    >
      <worker-form
        v-model="worker"
        :original="original"
        :readonly="!isAlowedToEdit"
        @submit="saveChanges"
        @change="change($event)"
      ></worker-form>
      <v-row v-if="isAlowedToEdit">
        <v-col cols="12">
          <v-divider></v-divider>
          <div class="d-flex flex-column flex-sm-row mt-5">
            <v-btn
              color="primary"
              outlined
              class="mr-0 mr-sm-2 my-2"
              @click="resetPassword"
            >
              Passwort zurücksetzten
            </v-btn>
            <v-btn
              color="red"
              class="white--text my-2"
              depressed
              @click="deleteWorker"
            >
              <v-icon class="mr-2">
                delete
              </v-icon>
              Hofmitarbeiter Löschen
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
    <div class="time-form">
      <v-container>
        <h2 class="display-1 py-4">
          Stundenangaben bearbeiten
        </h2>
      </v-container>
      <TimeView :worker-id="this.$route.params.id"></TimeView>
    </div>
  </fragment>
</template>

<script>
import TimeView from '@/views/Time'
import { confirmAction } from '@/utils'
import WorkerForm from '@/components/worker/WorkerForm'

export default {
  components: {
    TimeView,
    WorkerForm
  },
  data() {
    return {
      worker: null,
      original: null,
      apiUrl: `/workers/${this.$route.params.id}`,
      isLoadingRevert: false,
      isLoadingSave: false
    }
  },
  computed: {
    hasChanges() {
      return !this._.isEqual(this.worker, this.original)
    },
    isAlowedToEdit() {
      return this.$auth.user().hasPermission(['superadmin'], ['worker_write'])
    }
  },
  mounted() {
    this.axios.get(this.apiUrl).then(response => {
      this.worker = response.data
      this.original = this._.cloneDeep(this.worker)
    }).catch(() => {
      this.$store.dispatch('error', 'Hofmitarbeiter konnte nicht geladen werden')
    })
  },
  methods: {
    change(key) {
      this.$store.commit('isSaving', true)
      this.axios
        .patch(`workers/${this.$route.params.id}`, {
          [key]: this.worker[key]
        })
        .catch(() => {
          this.$swal('Fehler', 'Änderungen konnten nicht gespeichert werden. Bitte versuchen Sie es später erneut.', 'error')
        }).finally(() => {
          this.$store.commit('isSaving', false)
        })
    },
    resetPassword() {
      confirmAction('Der Mitarbeiter wird eine Email mit seinem neuen Passwort erhalten.', 'Ja, zurücksetzten').then(result => {
        if (result) {
          this.axios
            .post(`resetpassword/${this.$route.params.id}`)
            .then(() => {
              this.$swal(
                'Passwort wurde zurückgesetzt',
                `${this.worker.firstname} ${this.worker.lastname} hat eine Email mit dem neuen Passwort erhalten.`,
                'success',
              )
            })
            .catch(() => {
              this.$swal('Fehler', 'Passwort konnte aus einem unbekannten Grund nicht zurückgesetzt werden.', 'error')
            })
        }
      })
    },
    deleteWorker() {
      confirmAction('Willst du diesen Hofmitarbeiter wirklich löschen?').then(result => {
        if (result) {
          this.axios.delete(this.apiUrl).then(() => {
            this.$router.push('/worker')
          })
        }
      })
    },
    changeAuthrorization() {
      this.axios
        .patch(this.apiUrl, {
          type_id: this.worker.type_id === 3 ? 2 : 3
        })
        .then(response => {
          this.worker = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Berechtigung konnte nicht geändert werden', 'error')
        })
    },
    revertChanges() {
      this.isLoadingRevert = true
      this.axios.patch(this.apiUrl, this.workerPayload(this.original))
        .then(() => {
          this.worker = this._.cloneDeep(this.original)
        }).catch(() => {
          this.$store.dispatch('error', 'Änderungen konnten nicht mehr rückgängig gemacht werden')
        }).finally(() => {
          this.isLoadingRevert = false
        })
    },
    saveChanges() {
      if (this.isAlowedToEdit) {
        this.isLoadingSave = true
        this.axios.patch(this.apiUrl, this.workerPayload(this.worker))
          .then(() => {
            this.original = this._.cloneDeep(this.worker)
          }).catch(() => {
            this.$store.dispatch('error', 'Änderungen konnten nicht gespeichert werden')
          }).finally(() => {
            this.isLoadingSave = false
          })
      }
    },
    workerPayload(worker) {
      return {
        firstname: worker.firstname,
        lastname: worker.lastname,
        email: worker.email,
        role_id: worker.role_id,
        isActive: worker.isActive
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.delete_button {
  margin-top: 50px;
  text-align: center;
}

.time-form {
  background-color: white;
  margin-top: 40px;
}

@media only screen and (max-width: 600px) {
  .form {
    background-color: transparent;
    margin-top: 0;
  }
}
</style>
