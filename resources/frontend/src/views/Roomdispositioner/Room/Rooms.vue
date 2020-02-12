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
    <search-bar
      name="rooms"
      label="Raum suchen"
      v-model="roomsFiltered"
      @showDeleted="s => showDeleted = s"
      ref="searchBar"
    ></search-bar>
    <v-list class="pa-0 elevation-2">
      <v-list-item v-for="room of roomsFiltered" :key="room.id" :to="'/rooms/' + room.id">
        <v-list-item-content>
          <p class="mt-3">
            <strong>{{room.name}} | {{room.number}}</strong>
            {{room.location}}
          </p>
        </v-list-item-content>
        <v-list-item-action>{{activeBeds(room).length}} Betten / {{activeBeds(room).reduce((a,b) => a += b.places, 0)}} Plätze</v-list-item-action>

        <v-btn
          v-if="showDeleted && $auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          color="primary"
          class="ml-4"
          max-width="200"
          @click="e => restoreRoom(e, room)"
        >Wiederherstellen</v-btn>
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
import SearchBar from '@/components/general/SearchBar'

export default {
  name: 'Rooms',
  components: {
    SearchBar
  },
  data() {
    return {
      sortType: 'number',
      sortTypes: [
        { text: 'Name', value: 'name' },
        { text: 'Nummber', value: 'number' }
      ],
      roomsFiltered: [],
      showDeleted: false
    }
  },
  computed: {
    roomsSorted() {
      if (this.sortType === 'number') {
        return [...this.rooms].sort((a, b) => a.number - b.number)
      } else {
        return [...this.rooms].sort((a, b) => a.name.toLowerCase().localeCompare(b.name.toLowerCase()))
      }
    }
  },
  methods: {
    restoreRoom(event, room) {
      // dont go to the room page
      event.preventDefault()
      this.$refs.searchBar.restoreItem(room)
    },
    activeBeds(room) {
      return room.beds.filter(b => !b.pivot || !b.pivot.deleted_at)
    }
  }
}
</script>
