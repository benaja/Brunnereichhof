<template>
  <div class="container">
    <h1>Gäste-Übersicht</h1>
    <search-bar
      name="guests"
      label="Gast suchen"
      v-model="guestsFiltered"
      @showDeleted="s => showDeleted = s"
      ref="searchBar"
    ></search-bar>
    <v-expansion-panels>
      <v-expansion-panel v-for="guest in guestsFiltered" :key="guest.id">
        <v-expansion-panel-header hide-actions>
          <p class="pt-2 mt-1 header-text">
            <v-icon class="float-left">account_circle</v-icon>
            <span class="font-weight-bold pl-2">{{guest.name}}</span>
            <span class="font-italic hidden-xs-only">&nbsp; {{guest.callname}}</span>
          </p>
          <v-btn
            v-if="showDeleted"
            color="primary"
            max-width="200"
            @click="$refs.searchBar.restoreItem(guest)"
          >Wiederherstellen</v-btn>
          <v-btn v-else color="primary" max-width="100" :to="'/guest/' + guest.id">Details</v-btn>
        </v-expansion-panel-header>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-btn
      to="/employee/add?guest=true"
      fixed
      bottom
      right
      fab
      color="primary"
      v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
    >
      <v-icon>add</v-icon>
    </v-btn>
  </div>
</template>

<script>
import SearchBar from '@/components/general/SearchBar'

export default {
  name: 'guests',
  components: {
    SearchBar
  },
  data() {
    return {
      guestsFiltered: [],
      showDeleted: false
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

.filter {
  text-align: right;
}

.header-text {
  line-height: 1.6em;
  vertical-align: middle;
}
</style>

<style lang="scss">
.filter-controlls {
  .v-input__control {
    margin: 0 auto;
  }
}
</style>
