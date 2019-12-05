<template>
  <v-container>
    <v-row class="px-4 py-2">
      <v-col cols="12" md="2">
        <p class="mt-3 font-weight-bold subheading">Name</p>
      </v-col>
      <v-col cols="12" md="4">
        <edit-field
          v-model="room.name"
          @change="change('name')"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" md="2">
        <p class="mt-3 font-weight-bold subheading">Nummer</p>
      </v-col>
      <v-col cols="12" md="4">
        <edit-field
          v-model="room.number"
          @change="change('number')"
          type="number"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" md="2">
        <p class="mt-3 font-weight-bold subheading">Standort</p>
      </v-col>
      <v-col cols="12" md="10">
        <edit-field
          v-model="room.location"
          @change="change('location')"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <v-col cols="12" md="2">
        <p class="mt-3 font-weight-bold subheading">Kommentar</p>
      </v-col>
      <v-col cols="12" md="10">
        <edit-field
          v-model="room.comment"
          @change="change('comment')"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></edit-field>
      </v-col>
      <template v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])">
        <select-bed v-model="room.beds"></select-bed>
        <v-col cols="12" class="mt-2">
          <v-divider class="mb-2"></v-divider>
          <v-btn color="red" class="white--text" @click="deleteRoom">Raum Löschen</v-btn>
        </v-col>
      </template>
      <v-col cols="12">
        <h1>Statistiken</h1>
        <room-stats></room-stats>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import SelectBed from '@/components/Roomdispositioner/Bed/SelectBed'
import RoomStats from '@/components/Roomdispositioner/Room/RoomStats'

export default {
  name: 'SchowRoom',
  components: {
    SelectBed,
    RoomStats
  },
  data() {
    return {
      room: {}
    }
  },
  mounted() {
    this.axios.get('/rooms/' + this.$route.params.id).then(response => {
      this.room = response.data
    })
  },
  methods: {
    change(key) {
      this.axios.patch('/rooms/' + this.$route.params.id, {
        [key]: this.room[key]
      })
    },
    deleteRoom() {
      this.axios
        .delete('/rooms/' + this.$route.params.id)
        .then(() => {
          this.$router.push('/rooms')
        })
        .catch(error => {
          if (error.includes('Room is currently in use.')) {
            this.$swal('Raum wird momentan gebraucht', 'Der Raum ist momentan von einem Mitarbeiter belegt.', 'error')
          } else {
            this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
          }
        })
    }
  }
}
</script>

<style>
</style>
