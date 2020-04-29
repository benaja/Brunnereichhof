<template>
  <v-menu
    v-model="isOpen"
    :close-on-content-click="false"
    :nudge-width="300"
    offset-x
  >
    <template v-slot:activator="{ on }">
      <v-btn
        text
        color="primary"
        v-on="on"
      >
        Neue Rolle Erstellen
      </v-btn>
    </template>
    <v-card>
      <v-card-title class="headline">
        Rolle erstellen
      </v-card-title>
      <v-card-text>
        <v-form ref="form">
          <edit-field
            v-model="role.name"
            label="Name"
            :rules="[rules.required]"
          ></edit-field>
          <div class="scroll-container">
            <v-checkbox
              v-for="authorizationRule of authorizationRules"
              :key="authorizationRule.id"
              v-model="role.authorizationRules"
              :value="authorizationRule.id"
              :label="authorizationRule.name_de"
              class="ma-0"
              height="10"
            ></v-checkbox>
          </div>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          color="primary"
          @click="saveRole"
        >
          Speichern
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

<script>
export default {
  name: 'CreateRole',
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      authorizationRules: [],
      role: {
        name: null,
        authorizationRules: []
      },
      name: null,
      rules: {
        required: (v) => !!v || 'Dieses Feld muss vorhanden sein',
        minLenghtOne: (v) => v.length > 0 || 'Wähle mindestens ein Elment aus'
      }
    }
  },
  computed: {
    isOpen: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    }
  },
  mounted() {
    this.$store
      .dispatch('authorizationRules')
      .then((authorizationRules) => {
        this.authorizationRules = authorizationRules
      })
      .catch(() => {
        this.$swal('Fehler', 'Berechtigungen konnten nich abgerufen werden', 'error')
      })
  },
  methods: {
    saveRole() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('roles', this.role)
          .then((response) => {
            this.$store.commit('addRole', response.data)
            this.isOpen = false
            this.$refs.form.reset()
          })
          .catch(() => {
            this.$swal('Fehler', 'Es ist ein unerwarteter Fehler beim Speichern aufgetreten', 'error')
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.scroll-container {
  max-height: 400px;
  overflow-y: scroll;
}
</style>
