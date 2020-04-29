<template>
  <v-container>
    <v-row class="white px-4 py-2">
      <v-col
        cols="12"
        md="2"
      >
        <p class="mt-3 font-weight-bold subheading">
          Name
        </p>
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <edit-field
          v-model="inventar.name"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          @change="change('name')"
        ></edit-field>
      </v-col>
      <v-col
        cols="12"
        md="2"
      >
        <p class="mt-3 font-weight-bold subheading">
          Name
        </p>
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <edit-field
          v-model="inventar.price"
          type="number"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          @change="change('price')"
        ></edit-field>
      </v-col>
      <v-col
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        cols="12"
      >
        <v-btn
          color="red"
          class="white--text"
          @click="deleteInventar"
        >
          Löschen
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'ShowInventar',
  data() {
    return {
      inventar: {}
    }
  },
  mounted() {
    this.axios.get(`/inventars/${this.$route.params.id}`).then((response) => {
      this.inventar = response.data
    })
  },
  methods: {
    change(key) {
      this.axios.patch(`/inventars/${this.$route.params.id}`, {
        [key]: this.inventar[key]
      })
    },
    deleteInventar() {
      this.axios
        .delete(`/inventars/${this.$route.params.id}`)
        .then(() => {
          this.$router.push('/inventars')
        })
        .catch((error) => {
          if (error.includes('Integrity constraint violation')) this.$swal('Fehler', 'Inventar wird noch in einem Bett verwenden', 'error')
          else this.$swal('Fehler', 'Inventar konnte aus einem unbekanntem Grund nicht gelöscht werden', 'error')
        })
    }
  }
}
</script>

<style>
</style>
