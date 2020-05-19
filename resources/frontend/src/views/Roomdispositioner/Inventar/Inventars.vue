<template>
  <fragment>
    <navigation-bar
      title="Inventar"
      :loading="isLoading"
      color="blue"
    ></navigation-bar>
    <v-container>
      <v-list class="pa-0 elevation-2">
        <v-list-item
          v-for="inventar of inventars"
          :key="inventar.id"
          :to="'/inventars/' + inventar.id"
        >
          <v-list-item-content>
            <p class="mt-3">
              <strong>{{ inventar.name }}</strong>
              CHF {{ inventar.price }}
            </p>
          </v-list-item-content>
        </v-list-item>
      </v-list>
      <v-dialog
        v-model="addModel"
        width="900"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
            fixed
            bottom
            right
            fab
            color="blue"
            v-on="on"
          >
            <v-icon color="white">
              add
            </v-icon>
          </v-btn>
        </template>
        <add-inventar
          v-model="addModel"
          @add="add"
        ></add-inventar>
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import AddInventar from '@/components/Roomdispositioner/Inventar/AddInventar'

export default {
  components: {
    AddInventar
  },
  data() {
    return {
      inventars: [],
      addModel: false,
      isLoading: false
    }
  },
  mounted() {
    this.isLoading = true
    this.axios.get('/inventars').then(response => {
      this.inventars = response.data
    }).finally(() => {
      this.isLoading = false
    })
  },
  methods: {
    add(inventar) {
      this.inventars.push(inventar)
    }
  }
}
</script>
