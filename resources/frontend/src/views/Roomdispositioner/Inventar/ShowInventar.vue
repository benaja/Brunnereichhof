<template>
  <fragment>
    <navigation-bar
      title="Inventar"
      :loading="isLoading"
      color="blue"
    ></navigation-bar>
    <v-container>
      <inventar-form
        v-model="inventar"
        :readonly="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        @change="change($event)"
      ></inventar-form>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        color="red"
        class="white--text"
        depressed
        @click="deleteInventar"
      >
        Löschen
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import InventarForm from '@/components/forms/InventarForm'
import { confirmAction } from '@/utils'

export default {
  components: {
    InventarForm
  },
  data() {
    return {
      inventar: {},
      isLoading: false
    }
  },
  mounted() {
    this.isLoading = true
    this.axios.get(`/inventars/${this.$route.params.id}`).then(response => {
      this.inventar = response.data
      this.isLoading = false
    })
  },
  methods: {
    change(key) {
      this.$store.commit('isSaving', true)
      this.axios.patch(`/inventars/${this.$route.params.id}`, {
        [key]: this.inventar[key]
      }).finally(() => {
        this.$store.commit('isSaving', false)
      })
    },
    deleteInventar() {
      confirmAction().then(value => {
        if (value) {
          this.axios
            .delete(`/inventars/${this.$route.params.id}`)
            .then(() => {
              this.$router.push('/inventars')
            })
            .catch(error => {
              if (error.includes('Integrity constraint violation')) this.$swal('Fehler', 'Inventar wird noch in einem Bett verwenden', 'error')
              else this.$swal('Fehler', 'Inventar konnte aus einem unbekanntem Grund nicht gelöscht werden', 'error')
            })
        }
      })
    }
  }
}
</script>
