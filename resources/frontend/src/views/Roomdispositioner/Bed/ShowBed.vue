<template>
  <fragment>
    <navigation-bar
      title="Bett"
      :loading="isLoading"
      color="blue"
    ></navigation-bar>
    <v-container>
      <bed-form
        v-model="bed"
        :readonly="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        @change="change($event)"
      ></bed-form>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        color="red"
        class="white--text mt-5"
        depressed
        @click="deleteBed"
      >
        Löschen
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import { confirmAction } from '@/utils'
import BedForm from '@/components/forms/BedForm'

export default {
  name: 'ShowBed',
  components: {
    BedForm
  },
  data() {
    return {
      bed: {},
      isLoading: false
    }
  },
  mounted() {
    this.isLoading = true
    this.axios
      .get(`/beds/${this.$route.params.id}`)
      .then(response => {
        this.bed = response.data
      })
      .catch(() => {
        this.$store.dispatch('error', 'Bett konnte nicht geladen werden')
      })
      .finally(() => {
        this.isLoading = false
      })
  },
  methods: {
    change(key) {
      this.$store.commit('isSaving', true)
      this.axios.patch(`/beds/${this.$route.params.id}`, {
        [key]: this.bed[key]
      }).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Speichern')
      }).finally(() => {
        this.$store.commit('isSaving', false)
      })
    },
    deleteBed() {
      confirmAction().then(value => {
        if (value) {
          this.axios
            .delete(`/beds/${this.$route.params.id}`)
            .then(() => {
              this.$router.push('/beds')
            })
            .catch(error => {
              if (error.includes('Integrity constraint violation')) this.$swal('Fehler', 'Bett wird noch in einem Raum verwenden', 'error')
              else this.$swal('Fehler', 'Bett konnte aus einem unbekanntem Grund nicht gelöscht werden', 'error')
            })
        }
      })
    }
  }
}
</script>
