<template>
  <card-layout
    :loading="isLoading"
    color="blue"
    :saving="isSaving"
    title="Bett erstellen"
    @cancel="$emit('input', false)"
    @save="save"
  >
    <template>
      <bed-form
        ref="form"
        v-model="bed"
        @submit="save"
      ></bed-form>
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import { rules } from '@/utils'
import BedForm from '@/components/forms/BedForm'

export default {
  name: 'AddBed',
  components: {
    CardLayout,
    BedForm
  },
  props: {
    value: Boolean
  },
  data() {
    return {
      bed: {
        inventars: []
      },
      rules,
      isLoading: false,
      isSaving: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isSaving = true
        this.axios
          .post('/beds', this.bed)
          .then(response => {
            this.$store.commit('addBed', response.data)
            this.$emit('input', false)
          })
          .catch(() => {
            this.$swal('Fehler', 'Es ist ein unbekannter Fehler beim Speichern aufgetreten.', 'error')
          }).finally(() => {
            this.isSaving = false
          })
      }
    }
  }
}
</script>
