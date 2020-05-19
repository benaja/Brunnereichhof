<template>
  <fragment>
    <navigation-bar
      title="Raum"
      :loading="isLoading"
      color="blue"
    ></navigation-bar>
    <v-container>
      <v-row class="px-4 py-2">
        <room-form
          v-model="room"
          :readonly="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          @change="change($event)"
        ></room-form>
        <!-- <v-col
        cols="12"
        md="2"
      >
        <p class="mt-3 font-weight-bold subheading">
          Name
        </p>
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <edit-field
          v-model="room.name"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          @change="change('name')"
        ></edit-field>
      </v-col>
      <v-col
        cols="12"
        md="2"
      >
        <p class="mt-3 font-weight-bold subheading">
          Nummer
        </p>
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <edit-field
          v-model="room.number"
          type="number"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          @change="change('number')"
        ></edit-field>
      </v-col>
      <v-col
        cols="12"
        md="2"
      >
        <p class="mt-3 font-weight-bold subheading">
          Standort
        </p>
      </v-col>
      <v-col
        cols="12"
        md="10"
      >
        <edit-field
          v-model="room.location"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          @change="change('location')"
        ></edit-field>
      </v-col>
      <v-col
        cols="12"
        md="2"
      >
        <p class="mt-3 font-weight-bold subheading">
          Kommentar
        </p>
      </v-col>
      <v-col
        cols="12"
        md="10"
      >
        <edit-field
          v-model="room.comment"
          color="blue"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          @change="change('comment')"
        ></edit-field>
      </v-col> -->
        <!-- <v-col
        cols="12"
        md="6"
      >
        <select-bed
          v-model="room.beds"
          :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        ></select-bed>
      </v-col> -->
        <v-col cols="12">
          <select-images
            v-model="room.images"
            upload-on-change
            :upload-url="`/rooms/${$route.params.id}/images`"
            :disabled="!$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          ></select-images>
        </v-col>
        <v-col cols="12"></v-col>
        <v-col
          v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          cols="12"
          class="mt-2"
        >
          <v-divider class="mb-2"></v-divider>
          <v-btn
            color="red"
            class="white--text"
            depressed
            @click="deleteRoom"
          >
            Raum Löschen
          </v-btn>
        </v-col>
        <v-col cols="12">
          <h1>Statistiken</h1>
          <room-stats></room-stats>
        </v-col>
      </v-row>
    </v-container>
  </fragment>
</template>

<script>
import RoomStats from '@/components/Roomdispositioner/Room/RoomStats'
import SelectImages from '@/components/Roomdispositioner/Room/SelectImages'
import RoomForm from '@/components/forms/RoomForm'
import { confirmAction } from '@/utils'

export default {
  components: {
    RoomStats,
    SelectImages,
    RoomForm
  },
  data() {
    return {
      room: {},
      isLoading: false
    }
  },
  mounted() {
    this.isLoading = true
    this.axios
      .get(`/rooms/${this.$route.params.id}`)
      .then(response => {
        this.room = response.data
      })
      .catch(() => {
        this.$store.dispatch('error', 'Raum konnte nicht geladen werden')
      })
      .finally(() => {
        this.isLoading = false
      })
  },
  methods: {
    change(key) {
      this.$store.commit('isSaving', true)
      this.axios.patch(`/rooms/${this.$route.params.id}`, {
        [key]: this.room[key]
      }).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Speichern')
      }).finally(() => {
        this.$store.commit('isSaving', false)
      })
    },
    deleteRoom() {
      confirmAction().then(value => {
        if (value) {
          this.axios
            .delete(`/rooms/${this.$route.params.id}`)
            .then(() => {
              this.$router.push('/rooms')
            })
            .catch(error => {
              if (error.includes('Room is currently in use.')) {
                this.$swal('Raum wird momentan gebraucht', 'Der Raum ist momentan oder in Zukunft von einem Mitarbeiter belegt.', 'error')
              } else {
                this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
              }
            })
        }
      })
    }
  }
}
</script>
