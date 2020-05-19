<template>
  <card-layout
    color="blue"
    :saving="isSaving"
    title="Inventar hinzufügen"
    @save="save"
    @cancel="$emit('input', false)"
  >
    <template>
      <inventar-form
        ref="form"
        v-model="inventar"
        @submit="save"
      >
      </inventar-form>
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import InventarForm from '@/components/forms/InventarForm'

export default {
  naem: 'AddInventar',
  components: {
    CardLayout,
    InventarForm
  },
  props: {
    value: Boolean
  },
  data() {
    return {
      inventar: {},
      isSaving: false
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
        this.isSaving = true
        this.axios
          .post('/inventars', this.inventar)
          .then(response => {
            this.$refs.form.reset()
            this.$emit('input', false)
            this.$emit('add', response.data)
          })
          .catch(() => {
            this.$swal('Fehler', 'Inventar konnte nicht erstellt werden.', 'error')
          }).finally(() => {
            this.isSaving = false
          })
      }
    }
  }
}
</script>
