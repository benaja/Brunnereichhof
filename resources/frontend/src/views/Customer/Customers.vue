<template>
  <div class="container">
    <h1>Kunden-Übersicht</h1>
    <search-bar
      ref="searchBar"
      v-model="customersFiltered"
      name="customers"
      label="Kunden suchen"
      @showDeleted="s => (showDeleted = s)"
    ></search-bar>
    <v-expansion-panels>
      <v-expansion-panel
        v-for="(customer, index) in customersFiltered"
        :key="index"
      >
        <v-expansion-panel-header hide-actions>
          <p class="header-text">
            <v-icon class="account-icon">
              account_circle
            </v-icon>
            <span class="font-weight-bold">{{ customer.lastname }} {{ customer.firstname }}</span>
            <span
              class="font-italic hidden-xs-only"
            >&nbsp; {{ customer.address.street }}, {{ customer.address.place }}
              {{ customer.address.plz }}</span>
          </p>
          <v-btn
            v-if="showDeleted && $auth.user().hasPermission(['superadmin'], ['customer_write'])"
            max-width="200"
            color="primary"
            @click="e => restoreCustomer(e, customer)"
          >
            Wiederherstellen
          </v-btn>
          <v-btn
            v-else-if="!showDeleted"
            max-width="100"
            color="primary"
            :to="'/customer/' + customer.id"
          >
            Details
          </v-btn>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <v-row wrap>
            <v-col
              cols="12"
              md="6"
              lg="4"
            >
              <h4>Adresse</h4>
              <p>{{ customer.address.street }}</p>
              <p>{{ customer.address.place }} {{ customer.address.plz }}</p>
            </v-col>
            <v-col
              cols="12"
              md="6"
              lg="4"
            >
              <h4>Telefon</h4>
              <p>Mobile: {{ customer.mobile }}</p>
              <p>Festnetz: {{ customer.phone }}</p>
            </v-col>
            <v-col
              cols="12"
              md="6"
              lg="4"
            >
              <h4>Username und E-Mail</h4>
              <p>{{ customer.username }}</p>
              <p>{{ customer.email }}</p>
            </v-col>
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-btn
      v-if="$auth.user().hasPermission(['superadmin'], ['customer_write'])"
      to="/customer/add"
      fixed
      bottom
      right
      fab
      color="primary"
    >
      <v-icon>add</v-icon>
    </v-btn>
  </div>
</template>

<script>
import SearchBar from '@/components/general/SearchBar'

export default {
  name: 'Customers',
  components: {
    SearchBar
  },
  data() {
    return {
      showDeleted: false,
      customersFiltered: []
    }
  },
  methods: {
    restoreCustomer(event, customer) {
      this.$refs.searchBar.restoreItem(customer)
    }
  }
}
</script>

<style lang="scss" scoped>
#addbutton {
  position: fixed;
  bottom: 50px;
  right: 50px;
}

.switch {
  margin-top: 1em;
}

.account-icon {
  margin-right: 10px;
  float: left;
}

.content {
  padding-left: 10px;
}

.header-text {
  float: left;
  margin: 0;
  margin-top: 5px;
  line-height: 1.6em;
  vertical-align: middle;
}
</style>
