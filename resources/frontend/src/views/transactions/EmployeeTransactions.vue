<template>
  <fragment>
    <navigation-bar
      title="Vorschüsse Verwalten"
      :loading="isLoading.employees"
    >
    </navigation-bar>
    <v-container>
      <v-row>
        <v-col
          cols="12"
          sm="6"
          md="8"
          lg="9"
        >
          <v-autocomplete
            v-model="selectedEmployee"
            label="Mitarbeiter"
            :items="employees"
            item-value="id"
            item-text="name"
            no-data-text="keine Daten"
            autocomplete="off"
            return-object
            clearable
          ></v-autocomplete>
        </v-col>
        <v-col
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <v-switch
            v-model="onlyActive"
            label="Nur Aktive Mitarbeiter"
          ></v-switch>
        </v-col>
      </v-row>

      <v-divider class="mb-10"></v-divider>

      <template v-if="selectedEmployee">
        <add-transaction
          v-model="transaction"
          @created="transactionCreated"
        ></add-transaction>
        <v-expansion-panels class="mt-10">
          <v-expansion-panel>
            <v-expansion-panel-header>
              Saldo
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <p v-if="saldo !== null">
                <strong>Saldo: </strong>{{ saldo }} CHF
              </p>
              <v-btn
                text
                color="primary"
                :loading="loadingSaldo"
                @click="toggleSaldo"
              >
                {{ saldo === null ? 'Saldo anzeigen' : 'Saldo verbergen' }}
              </v-btn>
            </v-expansion-panel-content>
          </v-expansion-panel>
          <v-expansion-panel>
            <v-expansion-panel-header>
              Vorschüsse anzeigen
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-btn
                v-if="transactions === null"
                text
                color="primary"
                :loading="loadingTransactions"
                @click="transactions = []"
              >
                Vorschüsse anzeigen
              </v-btn>
              <template v-else>
                <transactions-table
                  :loading="loadingTransactions"
                  :transactions="transactions"
                  :meta="transactionsMeta"
                  @update="transactionUpdated"
                  @delete="transactionUpdated"
                  @pagination="loadTransactions"
                ></transactions-table>
                <v-btn
                  text
                  color="primary"
                  @click="transactions = null"
                >
                  Vorschüsse verbergen
                </v-btn>
              </template>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </template>
    </v-container>
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'
import AddTransaction from '@/components/transactions/AddTransaction'
import TransactionsTable from '@/components/transactions/TransactionsTable'

export default {
  components: {
    AddTransaction,
    TransactionsTable
  },
  data() {
    return {
      selectedEmployee: null,
      transaction: {
        employee_id: null,
        date: this.$moment().format('YYYY-MM-DD')
      },
      loadingSaldo: false,
      saldo: null,
      transactions: null,
      transactionsMeta: {},
      loadingTransactions: false,
      onlyActive: true
    }
  },
  computed: {
    ...mapGetters(['isLoading']),
    employees() {
      return this.$store.getters[this.onlyActive ? 'activeEmployees' : 'employees']
    }
  },
  watch: {
    selectedEmployee() {
      if (this.selectedEmployee) {
        this.transaction.employee_id = this.selectedEmployee.id
      }

      this.saldo = null
      this.transactions = null
      this.transactionsMeta = {}
    }
  },
  async mounted() {
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTransactionTypes')
  },
  methods: {
    loadSaldo() {
      this.loadingSaldo = true
      this.axios.get(`employees/${this.selectedEmployee.id}/transactions/sum`).then(response => {
        this.saldo = response.data.data
      }).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Laden des Saldos')
      }).finally(() => {
        this.loadingSaldo = false
      })
    },
    toggleSaldo() {
      if (this.saldo === null) {
        this.loadSaldo()
      } else {
        this.saldo = null
      }
    },
    loadTransactions(pagination = {}) {
      this.loadingTransactions = true
      this.axios.get(`employees/${this.selectedEmployee.id}/transactions`, {
        params: {
          page: pagination.page,
          per_page: pagination.itemsPerPage
        }
      }).then(response => {
        this.transactions = response.data.data
        this.transactionsMeta = response.data.meta
      }).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Laden der Vorschüsse')
      }).finally(() => {
        this.loadingTransactions = false
      })
    },
    transactionUpdated() {
      if (this.saldo) {
        this.loadSaldo()
      }
    },
    async transactionCreated() {
      if (this.saldo !== null) {
        await this.loadSaldo()
      }
      if (this.transactions !== null) {
        await this.loadTransactions()
      }
    }
  }
}
</script>
