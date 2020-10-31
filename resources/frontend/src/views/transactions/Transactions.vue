<template>
  <fragment>
    <navigation-bar
      title="Vorschüsse Anzeigen"
      :loading="isLoading.transactions"
    >
    </navigation-bar>
    <v-container>
      <v-text-field
        v-model="searchString"
        label="Suchen"
        prepend-icon="search"
        @input="debounceSearch"
      ></v-text-field>
      <transactions-table
        :transactions="transactions"
        :meta="transactionsMeta"
        with-employee
        @update="sortBy(options)"
        @delete="sortBy(options)"
        @pagination="paginate"
        @sortBy="sortBy"
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
      searchString: null,
      paginations: {},
      options: {},
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
    ...mapGetters(['transactions', 'isLoading', 'transactionsMeta']),
    debounceSearch() {
      return this._.debounce(() => {
        this.sortBy({
          ...this.options,
          page: 1
        })
      }, 300)
    }
  },
  methods: {
    updateTransactions() {
      this.editTransaction = null
      this.$store.dispatch('fetchTransactions', this.paginations)
    },
    paginate(paginations) {
      this.paginations = paginations
    },
    sortBy(options) {
      this.options = options
      this.$store.dispatch('fetchTransactions', {
        ...options,
        search: this.searchString
      })
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
