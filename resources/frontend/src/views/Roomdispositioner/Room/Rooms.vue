<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="9">
        <h1>Räume</h1>
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          v-model="sortType"
          :items="sortTypes"
          outline
          color="blue"
          single-line
          prepend-inner-icon="sort"
        ></v-select>
      </v-col>
    </v-row>
    <v-list class="pa-0 elevation-2">
      <v-list-item v-for="room of roomsSorted" :key="room.id" :to="'/rooms/' + room.id">
        <v-list-item-content>
          <p class="mt-3">
            <strong>{{room.name}} | {{room.number}}</strong>
            {{room.location}}
          </p>
        </v-list-item-content>
        <v-list-item-action>{{room.beds.length}} Betten / {{room.beds.reduce((a,b) => a += b.places, 0)}} Plätze</v-list-item-action>
      </v-list-item>
    </v-list>
    <v-btn
      to="/rooms/add"
      fixed
      bottom
      right
      fab
      color="blue"
      v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
    >
      <v-icon color="white">add</v-icon>
    </v-btn>
  </v-container>
</template>

<script>
export default {
  name: 'Rooms',
  data() {
    return {
      rooms: [],
      sortType: 'number',
      sortTypes: [{ text: 'Name', value: 'name' }, { text: 'Nummber', value: 'number' }]
    }
  },
  mounted() {
    this.axios.get('/rooms').then(response => {
      this.rooms = response.data
    })
  },
  computed: {
    roomsSorted() {
      if (this.sortType === 'number') {
        return [...this.rooms].sort((a, b) => a.number - b.number)
      } else {
        return [...this.rooms].sort((a, b) => a.name.toLowerCase().localeCompare(b.name.toLowerCase()))
      }
    }
  }
}
</script>

<style>
</style>
