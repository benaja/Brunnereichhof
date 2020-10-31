<template>
  <fragment>
    <navigation-bar
      title="Vorschüsse Anzeigen"
      :loading="isLoading.transactions || loadingStats"
    >
    </navigation-bar>
    <v-container>
      <v-row>
        <v-col
          cols="12"
          md="4"
        >
          <stat-card
            label="Total Positiv"
            :amount="stats.positive"
            icon="arrow_upward"
          ></stat-card>
        </v-col>
        <v-col
          cols="12"
          md="4"
        >
          <stat-card
            label="Total Negativ"
            :amount="stats.negative"
            icon="arrow_downward"
          ></stat-card>
        </v-col>
        <v-col
          cols="12"
          md="4"
        >
          <stat-card
            label="Total Über Alles"
            :amount="stats.total"
            icon="import_export"
          ></stat-card>
        </v-col>
      </v-row>
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
        @update="updateData(options)"
        @delete="updateData(options)"
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
import StatCard from '@/components/transactions/StatCard'

export default {
  components: {
    TransactionsTable,
    StatCard
  },
  data() {
    return {
      editTransaction: null,
      searchString: null,
      loadingStats: false,
      paginations: {},
      options: {},
      stats: {},
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
  mounted() {
    this.getStats()
  },
  methods: {
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
    },
    getStats() {
      this.loadingStats = true
      this.axios.get('transactions/stats').then(({ data }) => {
        this.stats = data.data
      }).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Laden der Statistiken')
      }).finally(() => {
        this.loadingStats = false
      })
    },
    updateData() {
      this.sortBy(this.options)
      this.getStats()
    }
  }
}
</script>
