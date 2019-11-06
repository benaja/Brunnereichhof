<template>
  <v-container class="form">
    <v-row>
      <v-col cols="12" class="mb-3">
        <h2 class="text-left display-1">Informationen</h2>
        <v-divider></v-divider>
      </v-col>
      <v-col cols="12" sm="6">
        <edit-field
          label="Vorname"
          v-model="worker.firstname"
          @change="change('firstname')"
          :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" sm="6">
        <edit-field
          label="Nachname"
          v-model="worker.lastname"
          @change="change('lastname')"
          :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" sm="6">
        <edit-field
          label="Email"
          v-model="worker.email"
          @change="change('email')"
          :readonly="!$auth.user().hasPermission(['superadmin'], ['worker_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" sm="6">
        <select-role v-model="worker.role_id" @change="change('role_id')"></select-role>
      </v-col>
      <template v-if="$auth.user().hasPermission(['superadmin'], ['worker_write'])">
        <v-col cols="12" class="mb-3">
          <h2 class="text-left display-1">Aktionen</h2>
          <v-divider></v-divider>
        </v-col>
        <v-col cols="12" md="6">
          <v-btn color="primary" @click="resetPassword" text>Passwort zurücksetzten</v-btn>
        </v-col>
        <!-- <v-col cols="12" md="6">
        <v-btn
          color="primary"
          @click="changeAuthrorization"
          text
        >{{worker.type_id === 3 ? 'Zu Admin bevördern' : 'Zu normalem Hofmitarbeiter degradieren'}}</v-btn>
        </v-col>-->
        <v-col cols="12" md="6">
          <p class="text-left">
            <v-btn color="red" class="white--text" @click="deleteWorker">Hofmitarbeiter Löschen</v-btn>
          </p>
        </v-col>
      </template>
    </v-row>
  </v-container>
</template>

<script>
import SelectRole from '@/components/Authorization/SelectRole'

export default {
  name: 'worker',
  components: {
    SelectRole
  },
  data() {
    return {
      worker: {},
      apiUrl: process.env.VUE_APP_API_URL + 'worker/' + this.$route.params.id,
      outline: {
        firstname: true,
        lastname: false,
        email: false
      }
    }
  },
  mounted() {
    this.axios.get(this.apiUrl).then(response => {
      this.worker = response.data
    })
  },
  methods: {
    change(key) {
      this.axios
        .patch('/worker/' + this.$route.params.id, {
          [key]: this.worker[key]
        })
        .catch(() => {
          this.$swal('Fehler', 'Änderungen konnten nicht gespeichert werden. Bitte versuchen Sie es später erneut.', 'error')
        })
    },
    resetPassword() {
      this.axios
        .post(process.env.VUE_APP_API_URL + 'resetpassword/' + this.$route.params.id)
        .then(() => {
          this.$swal(
            'Passwort wurde zurückgesetzt',
            `${this.worker.firstname} ${this.worker.lastname} hat eine Email mit dem neuen Passwort erhalten.`,
            'success'
          )
        })
        .catch(() => {
          this.$swal('Fehler', 'Passwort konnte aus einem unbekannten Grund nicht zurückgesetzt werden.', 'error')
        })
    },
    deleteWorker() {
      this.axios.delete(this.apiUrl).then(() => {
        this.$router.push('/worker')
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
    }
  }
}
</script>

<style lang="scss" scoped>
h2 {
  text-align: center;
}

.delete_button {
  margin-top: 50px;
  text-align: center;
}

.form {
  background-color: white;
  margin-top: 20px;
}

@media only screen and (max-width: 600px) {
  .form {
    background-color: transparent;
    margin-top: 0;
  }
}
</style>
