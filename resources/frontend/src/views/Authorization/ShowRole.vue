<template>
  <v-container>
    <edit-field
      v-model="role.name"
      label="name"
      @change="updateRole"
    ></edit-field>
    <v-checkbox
      v-for="authorizationRule of authorizationRules"
      :key="authorizationRule.id"
      v-model="role.selectedAuthorizationRules"
      :value="authorizationRule.id"
      :label="authorizationRule.name_de"
      class="ma-0"
      height="10"
      @change="updateRole"
    ></v-checkbox>
    <v-btn
      color="red"
      class="white--text"
      @click="deleteRole"
    >
      Rolle Löschen
    </v-btn>
  </v-container>
</template>

<script>
export default {
  name: 'ShowRole',
  data() {
    return {
      role: {},
      authorizationRules: {}
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios
      .get(`roles/${this.$route.params.id}`)
      .then((response) => {
        this.$store.dispatch('authorizationRules').then((authorizationRules) => {
          this.authorizationRules = authorizationRules
          this.$store.commit('isLoading', false)
        })
        response.data.selectedAuthorizationRules = response.data.authorization_rules
          .map((r) => r.id)

        this.role = response.data
      })
      .catch(() => {
        this.$store.commit('isLoading', false)
        this.$swal('Fehler', 'Rolle konnte nicht abgerufen werden', 'error')
      })
  },
  methods: {
    updateRole() {
      this.$store.dispatch('updateRole', this.role).catch(() => {
        this.$swal('Fehler', 'Die Änderungen konnten nicht gespeichert werden', 'error')
      })
    },
    deleteRole() {
      this.$store
        .dispatch('deleteRole', this.role.id)
        .then(() => this.$router.push('/roles'))
        .catch((error) => {
          if (error.includes('Role still has one or more users')) {
            this.$swal('Rolle besitzt noch Benutzer', 'Stelle sicher, dass keine Benutzer diese Rolle besitzt, damit du sie löschen kannst.', 'error')
          } else {
            this.$swal('Fehler', 'Die Rolle konnte nicht gelöscht werden', 'error')
          }
        })
    }
  }
}
</script>

<style>
</style>
