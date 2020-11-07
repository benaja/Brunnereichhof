<template>
  <v-expansion-panel>
    <v-expansion-panel-header
      hide-actions
      :color="source.is_blacklisted ? 'grey darken-4' : null"
      :class="{'white--text': source.is_blacklisted}"
    >
      <p class="header-text ">
        <v-icon
          class="account-icon"
          :color="source.is_blacklisted ? 'white' : null"
        >
          account_circle
        </v-icon>
        <span class="font-weight-bold">{{ source.customer_number }}<span v-if="source.customer_number">, </span>{{ source.lastname }} {{ source.firstname }}</span>
        <span
          class="font-italic hidden-xs-only"
        >&nbsp; {{ source.address.street }}<span v-if="source.address.street">, </span>{{ source.address.place }}
          {{ source.address.plz }}</span>
      </p>
      <v-btn
        v-if="showDeleted && $auth.user().hasPermission(['superadmin'], ['customer_write'])"
        max-width="200"
        color="primary"
        depressed
        @click="e => restoreCustomer(e, source)"
      >
        Wiederherstellen
      </v-btn>
      <v-btn
        v-else-if="!showDeleted"
        depressed
        max-width="100"
        color="primary"
        :to="'/customer/' + source.id"
      >
        Details
      </v-btn>
    </v-expansion-panel-header>
    <v-expansion-panel-content
      :color="source.is_blacklisted ? 'grey darken-4' : null"
      :class="{'white--text': source.is_blacklisted}"
    >
      <v-row wrap>
        <v-col
          cols="12"
          md="6"
          lg="4"
        >
          <h4>Adresse</h4>
          <p>{{ source.address.street }}</p>
          <p>{{ source.address.place }} {{ source.address.plz }}</p>
        </v-col>
        <v-col
          cols="12"
          md="6"
          lg="4"
        >
          <h4>Telefon</h4>
          <p>Mobile: {{ source.mobile }}</p>
          <p>Festnetz: {{ source.phone }}</p>
        </v-col>
        <v-col
          cols="12"
          md="6"
          lg="4"
        >
          <h4>Benutzername (Knr.) und E-Mail</h4>
          <p>{{ source.username }}</p>
          <p>{{ source.email }}</p>
        </v-col>
      </v-row>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>

<script>
export default {
  props: {
    source: {
      type: Object,
      default: () => ({})
    },
    restoreCustomer: {
      type: Function,
      default: () => {}
    },
    showDeleted: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.account-icon {
  margin-right: 10px;
  float: left;
}


.header-text {
  float: left;
  margin: 0;
  margin-top: 5px;
  line-height: 1.6em;
  vertical-align: middle;
}
</style>
