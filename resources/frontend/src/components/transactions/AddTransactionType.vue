<template>
  <card-layout
    :loading="isLoading"
    color="primary"
    :saving="isSaving"
    title="Vorschuss Typ erstellen"
    @cancel="cancel"
    @save="save"
  >
    <template>
      <transaction-type-form
        ref="form"
        v-model="transactionType"
        @submit="save"
      ></transaction-type-form>
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import TransactionTypeForm from '@/components/transactions/TransactionTypeForm'

export default {
  components: {
    CardLayout,
    TransactionTypeForm
  },
  data() {
    return {
      transactionType: {},
      isLoading: false,
      isSaving: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isSaving = true
        this.axios.post('transaction-types', {
          ...this.transactionType,
          is_positive: this.transactionType.is_positive || false
        }).then(response => {
          this.$emit('add', response.data.data)
          this.$refs.form.reset()
          this.$emit('input', false)
        }).catch(() => {
          this.$store.dispatch('error', 'Speichern fehlgeschlagen')
        }).finally(() => {
          this.isSaving = false
        })
      }
    },
    cancel() {
      this.$refs.form.reset()
      this.$emit('input', false)
    }
  }
}
</script>

<style>

</style>
