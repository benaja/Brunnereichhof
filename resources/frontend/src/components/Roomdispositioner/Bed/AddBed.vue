<template>
  <card-layout @cancel="$emit('input', false)" @save="save" color="blue">
    <template>
      <v-form ref="form" lazy-validation>
        <h2>Bett erstellen</h2>
        <v-text-field label="Name" v-model="bed.name" :rules="rules.required" color="blue"></v-text-field>
        <v-text-field label="Breite" v-model="bed.width" :rules="rules.required" color="blue"></v-text-field>
        <v-text-field
          type="number"
          label="Anzahl Plätze"
          v-model="bed.places"
          :rules="rules.required"
          color="blue"
        ></v-text-field>
        <v-textarea label="Kommentar" v-model="bed.comment" rows="1" auto-grow color="blue"></v-textarea>
        <select-inventar v-model="bed.inventars" :bed="bed"></select-inventar>
      </v-form>
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import SelectInventar from '@/components/Roomdispositioner/Inventar/SelectInventar'

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
      rules: {
        required: [v => !!v || 'Dieses Feld muss vorhanden sein']
      }
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('/beds', this.bed)
          .then(response => {
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

<style>
</style>
