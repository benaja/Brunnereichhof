<template>
  <fragment>
    <navigation-bar
      title="Vorschüsse Verwalten"
      :loading="isLoading.employees || isLoading.workers"
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
            v-model="selectedUser"
            label="Mitarbeiter"
            :items="filteredUsers"
            item-value="id"
            item-text="name"
            no-data-text="keine Daten"
            autocomplete="off"
            return-object
            clearable
          >
            <template v-slot:item="{item}">
              {{ item.name }}

              <v-chip
                v-if="item.type_id === UserType.Worker"
                class="ml-2"
                small
              >
                {{ $t('Hofmitarbeiter') }}
              </v-chip>
            </template>
          </v-autocomplete>
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

      <template v-if="selectedUser">
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
                <strong>Saldo: </strong>{{ saldo | round }} CHF
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
        <div class="text-center mt-10 mb-5">
          <v-btn
            depressed
            color="primary"
            :loading="isLoadingPdf"
            @click="generatePdf"
          >
            Pdf erstellen
          </v-btn>
        </div>
      </template>
    </v-container>
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'
import { downloadFile, UserType } from '@/utils'
import AddTransaction from '@/components/transactions/AddTransaction'
import TransactionsTable from '@/components/transactions/TransactionsTable'

export default {
  components: {
    AddTransaction,
    TransactionsTable
  },
  data() {
    return {
      selectedUser: null,
      transaction: {
        transactionable_id: null,
        date: this.$moment().format('YYYY-MM-DD')
      },
      loadingSaldo: false,
      saldo: null,
      transactions: null,
      transactionsMeta: {},
      loadingTransactions: false,
      onlyActive: true,
      isLoadingPdf: false,
      UserType
    }
  },
  computed: {
    ...mapGetters(['isLoading', 'employeesAndWorkers']),
    filteredUsers() {
      return [...this.employeesAndWorkers].filter(u => !!u.isActive === this.onlyActive)
        .sort((a, b) => {
          const nameA = `${a.name}`.toLowerCase()
          const nameB = `${b.name}`.toLowerCase()

          if (nameA < nameB) return -1
          if (nameA > nameB) return 1
          return 0
        })
    }
  },
  watch: {
    selectedUser() {
      if (this.selectedUser) {
        this.transaction.user_id = this.selectedUser.id
      }

      this.saldo = null
      this.transactions = null
      this.transactionsMeta = {}
    }
  },
  async mounted() {
    await this.$store.dispatch('fetchUsers')
    await this.$store.dispatch('fetchTransactionTypes')
  },
  methods: {
    loadSaldo() {
      this.loadingSaldo = true
      this.axios.get(`users/${this.selectedUser.id}/transactions-sum`)
        .then(response => {
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
      this.axios.get(`users/${this.selectedUser.id}/transactions`, {
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
    },
    generatePdf() {
      this.isLoadingPdf = true
      downloadFile(`pdf/employees/${this.selectedUser.id}/saldo`).catch(() => {
        this.$store.dispatch('error', 'Pdf konnte nicht erstellt werden')
      }).finally(() => {
        this.isLoadingPdf = false
      })
    }
  }
}
</script>
