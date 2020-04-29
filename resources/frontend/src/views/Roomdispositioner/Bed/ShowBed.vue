<template>
  <v-container>
    <progress-linear :loading="isLoading" color="blue"></progress-linear>
    <v-row class="white px-4 py-2">
      <v-col cols="12" md="2" class="py-0">
        <p class="mt-3 font-weight-bold subheading">Name</p>
      </v-col>
      <v-col cols="12" md="4" class="py-0">
        <edit-field
          v-model="bed.name"
          @change="change('name')"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" md="2" class="py-0">
        <p class="mt-3 font-weight-bold subheading">Breite</p>
      </v-col>
      <v-col cols="12" md="4" class="py-0">
        <edit-field
          v-model="bed.width"
          @change="change('width')"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" md="2" class="py-0">
        <p class="mt-3 font-weight-bold subheading">PLätze</p>
      </v-col>
      <v-col cols="12" md="10" class="py-0">
        <edit-field
          v-model="bed.places"
          @change="change('places')"
          type="number"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" md="2" class="py-0">
        <p class="mt-3 font-weight-bold subheading">Kommentar</p>
      </v-col>
      <v-col cols="12" md="10" class="py-0">
        <edit-field
          v-model="bed.comment"
          @change="change('comment')"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <template v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])">
        <v-col cols="12">
          <select-inventar v-model="bed.inventars" :bed="bed"></select-inventar>
        </v-col>
        <v-col cols="12">
          <v-divider class="my-2"></v-divider>
          <v-btn color="red" class="white--text" @click="deleteBed">Löschen</v-btn>
        </v-col>
      </template>
    </v-row>
  </v-container>
</template>

<script>
import SelectInventar from '@/components/Roomdispositioner/Inventar/SelectInventar'

export default {
  name: 'ShowBed',
  components: {
    SelectInventar
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
      .get('/beds/' + this.$route.params.id)
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
      this.axios.patch('/beds/' + this.$route.params.id, {
        [key]: this.bed[key]
      })
    },
    deleteBed() {
      this.axios
        .delete('/beds/' + this.$route.params.id)
        .then(() => {
          this.$router.push('/beds')
        })
        .catch(error => {
          if (error.includes('Integrity constraint violation')) this.$swal('Fehler', 'Bett wird noch in einem Raum verwenden', 'error')
          else this.$swal('Fehler', 'Bett konnte aus einem unbekanntem Grund nicht gelöscht werden', 'error')
        })
    }
  }
}
</script>
