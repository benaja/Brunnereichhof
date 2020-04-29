<template>
  <card-layout
    :loading="isLoading"
    color="blue"
    @cancel="$emit('input', false)"
    @save="save"
  >
    <template>
      <v-form
        ref="form"
        lazy-validation
      >
        <h2>Bett erstellen</h2>
        <v-text-field
          v-model="bed.name"
          :rules="[rules.required]"
          label="Name*"
          color="blue"
        ></v-text-field>
        <v-text-field
          v-model="bed.width"
          :rules="[rules.required]"
          label="Breite*"
          color="blue"
        ></v-text-field>
        <v-text-field
          v-model="bed.places"
          :rules="[rules.required]"
          type="number"
          label="Anzahl Plätze*"
          color="blue"
        ></v-text-field>
        <v-textarea
          v-model="bed.comment"
          label="Kommentar"
          rows="1"
          auto-grow
          color="blue"
        ></v-textarea>
        <select-inventar
          v-model="bed.inventars"
          :bed="bed"
        ></select-inventar>
      </v-form>
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import SelectInventar from '@/components/Roomdispositioner/Inventar/SelectInventar'
import { rules } from '@/utils'

export default {
  name: 'AddBed',
  components: {
    CardLayout,
    SelectInventar
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
      isLoading: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('/beds', this.bed)
          .then((response) => {
            this.$store.commit('addBed', response.data)
            this.$emit('add', response.data)
            this.$emit('input', false)
          })
          .catch(() => {
            this.$swal('Fehler', 'Es ist ein unbekannter Fehler beim Speichern aufgetreten.', 'error')
          })
      }
    }
  }
}
</script>
