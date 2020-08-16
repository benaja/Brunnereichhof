<template>
  <fragment>
    <navigation-bar
      title="Vorschuss Manager"
      :loading="isLoading.employees"
    ></navigation-bar>
    <v-container>
      <v-data-table
        :items="activeEmployees"
        :headers="headers"
      >
        <template v-slot:item="{item}">
          <transaction-form v-model="item"></transaction-form>
        </template>
      </v-data-table>
    </v-container>
  </fragment>
</template>

<script>
import TransactionForm from '@/components/transactions/TransactionForm'
import { mapGetters } from 'vuex'

export default {
  components: {
    TransactionForm
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
      ]
    }
  },
  computed: {
    ...mapGetters(['activeEmployees', 'isLoading'])
  },
  async mounted() {
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTransactionTypes')
  }
}
</script>

<style>

</style>
