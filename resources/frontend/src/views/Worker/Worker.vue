<template>
  <div>
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
          @click="saveChanges"
        >
          Speichern
        </v-btn>
      </template>
    </navigation-bar>
    <v-container
      v-if="worker"
    >
      <v-form
        @keyup.native.enter="saveChanges"
      >
        <v-row>
          <v-col
            cols="12"
            sm="6"
          >
            <text-field
              v-model="worker.firstname"
              :original="original.firstname"
              :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
              label="Vorname"
              @change="change('firstname')"
            ></text-field>
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <text-field
              v-model="worker.lastname"
              :original="original.lastname"
              :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
              label="Nachname"
              @change="change('lastname')"
            ></text-field>
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <text-field
              v-model="worker.email"
              :original="original.email"
              :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
              label="Email"
              @change="change('email')"
            ></text-field>
          </v-col>
          <v-col
            cols="12"
            sm="6"
          >
            <select-role
              v-model="worker.role_id"
              :original="original.role_id"
              :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
              @change="change('role_id')"
            ></select-role>
          </v-col>
          <v-col cols="12">
            <v-switch
              v-model="worker.isActive"
              :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
              label="Aktiv"
              @change="change('isActive')"
            ></v-switch>
          </v-col>
          <template v-if="$auth.user().hasPermission(['superadmin'], ['worker_write'])">
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
          </template>
        </v-row>
      </v-form>
    </v-container>
    <div class="time-form">
      <v-container>
        <h2 class="display-1 py-4">
          Stundenangaben bearbeiten
        </h2>
      </v-container>
      <TimeView :worker-id="this.$route.params.id"></TimeView>
    </div>
  </div>
</template>

<script>
import SelectRole from '@/components/Authorization/SelectRole'
import TimeView from '@/views/Time'
import { confirmDelete } from '@/utils'
import { TextField } from '@/components/FormComponents'

export default {
  name: 'Worker',
  components: {
    SelectRole,
    TimeView,
    TextField
  },
  data() {
    return {
      worker: null,
      original: null,
      apiUrl: `/workers/${this.$route.params.id}`,
      outline: {
        firstname: true,
        lastname: false,
        email: false
      },
      isLoadingRevert: false
    }
  },
  computed: {
    hasChanges() {
      return !this._.isEqual(this.worker, this.original)
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
    },
    deleteWorker() {
      confirmDelete('Willst du diesen Hofmitarbeiter wirklich löschen?').then(result => {
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
      this.axios.patch(this.apiUrl, {
        firstname: this.original.firstname,
        lastname: this.original.lastname,
        email: this.original.email,
        role_id: this.original.role_id,
        isActive: this.original.isActive
      }).then(() => {
        this.worker = this._.cloneDeep(this.original)
      }).catch(() => {
        this.$store.dispatch('error', 'Änderungen konnten nicht mehr rückgängig gemacht werden')
      }).finally(() => {
        this.isLoadingRevert = false
      })
    },
    saveChanges() {
      this.original = this._.cloneDeep(this.worker)
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
