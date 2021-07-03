<template>
  <td
    :color="source.is_blacklisted ? 'grey darken-4' : null"
    :class="{
      'white--text': source.is_blacklisted,
      'grey darken-4': source.is_blacklisted
    }"
    colspan="5"
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
  </td>
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
