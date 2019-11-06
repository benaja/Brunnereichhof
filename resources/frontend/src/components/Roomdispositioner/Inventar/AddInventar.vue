<template>
  <card-layout @save="save" @cancel="$emit('input', false)" color="blue">
    <template>
      <v-form lazy-validation v-model="valid" ref="form">
        <h2>Inventar hinzufügen</h2>
        <v-text-field label="Name" v-model="inventar.name" :rules="rules.name" color="blue"/>
        <v-text-field
          label="Preis in CHF"
          type="number"
          v-model="inventar.price"
          :rules="rules.price"
          @keyup.13="save"
          color="blue"
        />
      </v-form>
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'

export default {
  naem: 'AddInventar',
  components: {
    CardLayout
  },
  props: {
    value: Boolean
  },
  data() {
    return {
      inventar: {},
      rules: {
        name: [v => !!v || 'Name muss vorhanden sein'],
        price: [v => !!v || 'Geben Sie einen Preis an']
      },
      valid: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('/inventars', this.inventar)
          .then(response => {
            this.$refs.form.reset()
            this.$emit('input', false)
            this.$emit('add', response.data)
          })
          .catch(() => {
            this.$swal('Fehler', 'Inventar konnte nicht erstellt werden.', 'error')
          })
      }
    }
  },
  watch: {
    value() {
      if (!this.value) {
        this.$refs.form.reset()
      }
    }
  }
}
</script>

<style>
</style>
