<template>
  <card-layout
    color="blue"
    @save="save"
    @cancel="$emit('input', false)"
  >
    <template>
      <v-form
        ref="form"
        v-model="valid"
        lazy-validation
      >
        <h2>Inventar hinzufügen</h2>
        <v-text-field
          v-model="inventar.name"
          label="Name"
          :rules="rules.name"
          color="blue"
        />
        <v-text-field
          v-model="inventar.price"
          label="Preis in CHF"
          type="number"
          :rules="rules.price"
          color="blue"
          @keyup.13="save"
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
        name: [(v) => !!v || 'Name muss vorhanden sein'],
        price: [(v) => !!v || 'Geben Sie einen Preis an']
      },
      valid: false
    }
  },
  watch: {
    value() {
      if (!this.value) {
        this.$refs.form.reset()
      }
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('/inventars', this.inventar)
          .then((response) => {
            this.$refs.form.reset()
            this.$emit('input', false)
            this.$emit('add', response.data)
          })
          .catch(() => {
            this.$swal('Fehler', 'Inventar konnte nicht erstellt werden.', 'error')
          })
      }
    }
  }
}
</script>

<style>
</style>
