<template>
  <card-layout
    color="primary"
    :saving="isSaving"
    title="Vorschuss Typ bearbeiten"
    @cancel="closeModal"
    @save="save"
  >
    <template slot="title-action">
      <v-btn
        icon
        @click="deleteTransactionType"
      >
        <v-icon>delete</v-icon>
      </v-btn>
    </template>
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
import { confirmAction } from '@/utils'

export default {
  components: {
    CardLayout,
    TransactionTypeForm
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      isSaving: false,
      transactionType: { ...this.value }
    }
  },
  watch: {
    value() {
      this.transactionType = { ...this.value }
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isSaving = true
        this.axios.put(`transaction-types/${this.transactionType.id}`, {
          ...this.transactionType,
          is_positive: this.transactionType.is_positive || false
        }).then(response => {
          this.$emit('update', response.data.data)
          this.closeModal()
        }).catch(() => {
          this.$store.dispatch('error', 'Speichern fehlgeschlagen')
        }).finally(() => {
          this.isSaving = false
        })
      }
    },
    closeModal() {
      this.$refs.form.reset()
      this.$emit('input', false)
    },
    deleteTransactionType() {
      confirmAction().then(value => {
        if (value) {
          this.axios.delete(`transaction-types/${this.transactionType.id}`).then(() => {
            this.$emit('update', null)
            this.closeModal()
          }).catch(() => {
            this.$store.dispatch('error', 'Es ist ein unbekannter Fehler aufgetreten')
          })
        }
      })
    }
  }
}
</script>

<style>

</style>
