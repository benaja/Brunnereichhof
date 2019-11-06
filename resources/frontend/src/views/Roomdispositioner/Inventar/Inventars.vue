<template>
  <v-container>
    <h1>Inventar</h1>
    <v-list class="pa-0 elevation-2">
      <v-list-item
        v-for="inventar of inventars"
        :key="inventar.id"
        :to="'/inventars/' + inventar.id"
      >
        <v-list-item-content>
          <p class="mt-3">
            <strong>{{inventar.name}}</strong>
            CHF {{inventar.price}}
          </p>
        </v-list-item-content>
      </v-list-item>
    </v-list>
    <v-menu
      :close-on-content-click="false"
      v-model="addModel"
      right
      max-width="400"
      min-width="400"
    >
      <template v-slot:activator="{ on }">
        <v-btn
          fixed
          bottom
          right
          fab
          color="blue"
          v-on="on"
          v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        >
          <v-icon color="white">add</v-icon>
        </v-btn>
      </template>
      <add-inventar v-model="addModel" @add="add"></add-inventar>
    </v-menu>
  </v-container>
</template>

<script>
import AddInventar from '@/components/Roomdispositioner/Inventar/AddInventar'

export default {
  name: 'Inventars',
  components: {
    AddInventar
  },
  data() {
    return {
      inventars: [],
      addModel: false
    }
  },
  mounted() {
    this.axios.get('/inventars').then(response => {
      this.inventars = response.data
    })
  },
  methods: {
    add(inventar) {
      this.inventars.push(inventar)
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
