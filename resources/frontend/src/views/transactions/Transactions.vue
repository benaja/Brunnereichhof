<template>
  <fragment>
    <navigation-bar
      title="Vorschüsse Anzeigen"
      :loading="isLoading.transactions"
    >
    </navigation-bar>
    <v-container>
      <transactions-table
        :transactions="transactions"
        :meta="transactionsMeta"
        with-employee
        @pagination="paginate"
      ></transactions-table>
    </v-container>
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'
import { confirmAction } from '@/utils'
import TransactionsTable from '@/components/transactions/TransactionsTable'

export default {
  components: {
    TransactionsTable
  },
  data() {
    return {
      editTransaction: null,
      paginations: {},
      headers: [
        {
          text: 'Mitarbeiter',
          value: 'employee.lastname'
        },
        {
          text: 'Datum',
          value: 'date'
        },
        {
          text: 'Vorschuss Typ',
          value: 'type.name'
        },
        {
          text: 'Menge in CHF',
          value: 'amount'
        },
        {
          text: 'Kommentar',
          value: 'comment'
        },
        {
          text: 'Aktionen'
        }
      ]
    }
  },
  computed: {
    ...mapGetters(['transactions', 'isLoading', 'transactionsMeta'])
  },
  methods: {
    updateTransactions() {
      this.editTransaction = null
      this.$store.dispatch('fetchTransactions', this.paginations)
    },
    paginate(paginations) {
      this.paginations = paginations
      this.$store.dispatch('fetchTransactions', paginations)
    },
    deleteTransaction(transaction) {
      confirmAction().then(value => {
        if (value) {
          this.axios.delete(`transactions/${transaction.id}`).then(() => {
            this.$store.dispatch('alert', { text: 'Vorschuss wurde erfolgreich gelöscht' })
            this.$store.dispatch('fetchTransactions', this.paginations)
          }).catch(() => {
            this.$store.dispatch('error', 'Vorschuss konnte nicht gelöscht werden')
          })
        }
      })
    }
  }
}
</script>
