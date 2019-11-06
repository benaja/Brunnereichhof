<template>
  <div>
    <h3>Inventar Gegenstände</h3>
    <div v-for="inventar of value" :key="inventar.id">
      <v-row>
        <v-col cols="6">
          <p class="my-3">
            {{inventar.name}}
            <span class="caption">CHF{{inventar.price}}</span>
          </p>
        </v-col>
        <v-col cols="6">
          <p class="text-right my-0">
            <span v-if="bed.places >= 2">1er Belegung</span>
            <v-btn text icon color="red" @click="remove(inventar)">
              <v-icon>remove</v-icon>
            </v-btn>
            {{inventar.pivot.amount}}
            <v-btn text icon color="primary" @click="addAmount(inventar, false)">
              <v-icon>add</v-icon>
            </v-btn>
          </p>
          <p class="text-right my-0" v-if="bed.places >= 2">
            2er Belegung
            <v-btn text icon color="red" @click="remove(inventar, true)">
              <v-icon>remove</v-icon>
            </v-btn>
            {{inventar.pivot.amount_2}}
            <v-btn text icon color="primary" @click="addAmount(inventar, true)">
              <v-icon>add</v-icon>
            </v-btn>
          </p>
        </v-col>
      </v-row>
    </div>
    <v-autocomplete
      v-model="searchItem"
      autocomplete="off"
      label="Inventar Suchen"
      :items="inventars"
      item-value="id"
      item-text="name"
      :loading="isLoading"
      :search-input.sync="search"
      @change="change"
      @focus="search = ''"
      color="blue"
    ></v-autocomplete>
    <v-menu :close-on-content-click="false" :nudge-width="200" offset-x v-model="addInventarModel">
      <template v-slot:activator="{ on }">
        <v-btn color="blue" class="white--text" v-on="on">Neues Inventar erstellen</v-btn>
      </template>
      <add-inventar v-model="addInventarModel" @add="inventar => add(inventar)"></add-inventar>
    </v-menu>
  </div>
</template>

<script>
import AddInventar from '@/components/Roomdispositioner/Inventar/AddInventar'

export default {
  name: 'SelectInventar',
  components: {
    AddInventar
  },
  props: {
    value: Array,
    bed: Object
  },
  data() {
    return {
      addInventarModel: false,
      inventars: [],
      selectedInventars: [],
      searchItem: null,
      search: null,
      isLoading: false,
      inventarsLoaded: false
    }
  },
  methods: {
    change() {
      if (this.value.find(i => i.id === this.searchItem)) {
        let inventar = this.value.find(i => i.id === this.searchItem)
        inventar.pivot.amount++
        inventar.pivot.amount_2++
      } else {
        let selectedInventar = this.inventars.find(i => i.id === this.searchItem)
        if (selectedInventar) {
          selectedInventar.pivot.amount = 1
          selectedInventar.pivot.amount_2 = 1
          this.value.push(selectedInventar)
        }
      }
      if (this.bed.id) {
        this.axios.patch(`/beds/${this.$route.params.id}/inventars/${this.searchItem}`)
      }
    },
    remove(inventar, removeAmount2 = false) {
      if (removeAmount2) {
        if (inventar.pivot.amount_2 > 0) inventar.pivot.amount_2--
      } else {
        inventar.pivot.amount--
        inventar.pivot.amount_2 = inventar.pivot.amount
        if (inventar.pivot.amount <= 0) {
          this.value.splice(this.value.indexOf(inventar), 1)
        }
      }
      if (this.bed.id) {
        this.axios.delete(`/beds/${this.$route.params.id}/inventars/${inventar.id}?removeAmount2=${removeAmount2}`)
      }
    },
    add(inventar) {
      inventar.pivot = {
        amount: 1,
        amount_2: 1
      }
      this.inventars.push(inventar)
      this.value.push(inventar)
      if (this.bed.id) {
        this.axios.patch(`/beds/${this.$route.params.id}/inventars/${inventar.id}`)
      }
    },
    addAmount(inventar, addAmount2 = false) {
      if (addAmount2) {
        inventar.pivot.amount_2++
      } else {
        inventar.pivot.amount++
        inventar.pivot.amount_2 = inventar.pivot.amount
      }
      if (this.bed.id) {
        this.axios.patch(`/beds/${this.$route.params.id}/inventars/${inventar.id}`, {
          addAmount2
        })
      }
    }
  },
  watch: {
    search() {
      if (this.inventarsLoaded) return
      if (this.isLoading) return

      this.isLoading = true

      this.axios.get('/inventars').then(response => {
        for (let inventar of response.data) {
          inventar.pivot = {
            amount: 0,
            amount_2: 0
          }
          this.inventars.push(inventar)
        }
        this.isLoading = false
        this.inventarsLoaded = true
      })
    }
  }
}
</script>

<style>
</style>
