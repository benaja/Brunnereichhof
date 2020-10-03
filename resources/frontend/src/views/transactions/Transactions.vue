<template>
  <fragment>
    <navigation-bar
      title="Vorschuss Manager"
      :loading="isLoading.employees"
    ></navigation-bar>
    <v-container>
      <search-bar
        ref="searchBar"
        v-model="employeesFiltered"
        name="employees"
        label="Mitarbeiter suchen"
        disable-deleted
        :items="mapedEmployees"
      >
      </search-bar>
      <v-data-table
        :items="employeesFiltered"
        :headers="headers"
      >
        <template v-slot:body="{items}">
          <transaction-form
            v-for="item of items"
            :key="item.id"
            :value="item"
          ></transaction-form>
        </template>
      </v-data-table>
    </v-container>
  </fragment>
</template>

<script>
import TransactionForm from '@/components/transactions/TransactionForm'
import SearchBar from '@/components/general/SearchBar'
import { mapGetters } from 'vuex'

export default {
  components: {
    TransactionForm,
    SearchBar
  },
  data() {
    return {
      headers: [
        {
          text: 'Name',
          value: 'name'
        },
        {
          text: 'Hinzufügen'
        },
        {
          text: 'Entfernen'
        },
        {
          text: 'Menge'
        },
        {
          text: 'Kommentar'
        }
      ],
      employeesFiltered: []
    }
  },
  computed: {
    ...mapGetters(['activeEmployees', 'isLoading']),
    mapedEmployees() {
      return this.activeEmployees.map(e => ({
        ...e,
        transaction: e.transaction || {}
      }))
    }
  },
  async mounted() {
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTransactionTypes')
  }
}
</script>

<style>

</style>
