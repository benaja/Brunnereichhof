<template>
  <fragment>
    <navigation-bar
      title="Räume"
      :loading="isLoading.rooms"
      color="blue"
    >
      <div class="ml-auto">
        <v-select
          v-model="sortType"
          :items="sortTypes"
          outlined
          color="blue"
          item-color="blue"
          prepend-inner-icon="sort"
          hide-details
          class="ml-auto"
        ></v-select>
      </div>
    </navigation-bar>
    <v-container>
      <search-bar
        ref="searchBar"
        v-model="roomsFiltered"
        name="rooms"
        label="Raum suchen"
        color="blue"
        :items="rooms"
        :custom-filter-function="filterActive"
        @showDeleted="s => showDeleted = s"
      >
        <v-switch
          slot="custom-filter"
          v-model="showActive"
          color="blue"
          label="Aktiv"
          :disabled="showDeleted"
        ></v-switch>
      </search-bar>
      <v-list class="pa-0 elevation-2">
        <v-list-item
          v-for="room of roomsSorted"
          :key="room.id"
          :to="'/rooms/' + room.id"
        >
          <v-list-item-content>
            <p class="mt-3">
              <strong>{{ room.name }} | {{ room.number }}</strong>
              {{ room.location }}
            </p>
          </v-list-item-content>
          <v-list-item-action>
            {{ activeBeds(room).length }} Betten /
            {{ activeBeds(room).reduce((a,b) => a += b.places, 0) }} Plätze
          </v-list-item-action>

          <v-btn
            v-if="showDeleted
              && $auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
            color="blue"
            class="ml-4 white--text"
            max-width="200"
            depressed
            @click="e => restoreRoom(e, room)"
          >
            Wiederherstellen
          </v-btn>
        </v-list-item>
      </v-list>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        to="/rooms/add"
        fixed
        bottom
        right
        fab
        color="blue"
      >
        <v-icon color="white">
          add
        </v-icon>
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import SearchBar from '@/components/general/SearchBar'
import { mapGetters } from 'vuex'

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
      showDeleted: false,
      showActive: true
    }
  },
  computed: {
    ...mapGetters({
      rooms: 'allRooms',
      isLoading: 'isLoading'
    }),
    roomsSorted() {
      if (this.sortType === 'number') {
        return [...this.roomsFiltered].sort((a, b) => a.number - b.number)
      }
      return [...this.roomsFiltered].sort((a, b) => {
        const nameA = a.name || ''
        const nameB = b.name || ''
        return nameA.toLowerCase().localeCompare(nameB.toLowerCase())
      })
    }
  },
  mounted() {
    this.$store.dispatch('fetchRooms')
  },
  methods: {
    restoreRoom(event, room) {
      // dont go to the room page
      event.preventDefault()
      this.$refs.searchBar.restoreItem(room)
    },
    activeBeds(room) {
      return room.beds.filter(b => !b.pivot || !b.pivot.deleted_at)
    },
    filterActive(room) {
      return !room.isActive === !this.showActive
    }
  }
}
</script>
