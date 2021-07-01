<template>
  <v-row>
    <v-col
      cols="12"
      md="6"
    >
      <p><strong>Saldo:</strong> {{ saldo | round }} CHF</p>
    </v-col>
    <v-col
      cols="12"
      md="6"
    >
      <v-btn
        depressed
        color="primary"
        :loading="isLoadingSaldoPdf"
        @click="generateSaldoPdf"
      >
        Saldo Pdf erstellen
      </v-btn>
    </v-col>
  </v-row>
</template>

<script>
import { downloadFile } from '@/utils'

export default {
  props: {
    saldo: {
      type: Number,
      default: 0
    },
    user: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      isLoadingSaldoPdf: false
    }
  },
  methods: {
    generateSaldoPdf() {
      this.isLoadingSaldoPdf = true
      downloadFile(`pdf/users/${this.user.id}/saldo`).catch(() => {
        this.$store.dispatch('error', 'Pdf konnte nicht erstellt werden')
      }).finally(() => {
        this.isLoadingSaldoPdf = false
      })
    }
  }
}
</script>

<style>

</style>
