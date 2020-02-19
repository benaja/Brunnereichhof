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
        <v-col cols="12" md="6">
          <select-bed v-model="room.beds"></select-bed>
        </v-col>
        <v-col cols="12">
          <v-row>
            <v-col
              v-for="(image, index) of room.images"
              :key="image.id"
              class="d-flex child-flex"
              cols="4"
            >
              <v-card flat tile class="d-flex image-card">
                <v-img
                  :src="image.url"
                  aspect-ratio="1"
                  class="grey lighten-2"
                  @click="
                    selectedImage = index
                    galleryDialog = true
                  "
                >
                  <template v-slot:placeholder>
                    <v-row class="fill-height ma-0" align="center" justify="center">
                      <v-progress-circular indeterminate color="grey lighten-5"></v-progress-circular>
                    </v-row>
                  </template>
                </v-img>
                <v-btn fab dark class="delete-icon" @click="deleteImage(image)">
                  <v-icon>delete</v-icon>
                </v-btn>
              </v-card>
            </v-col>
            <file-upload @uploaded="addImages"></file-upload>
          </v-row>
        </v-col>
        <v-col cols="12"></v-col>
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
    <v-dialog v-model="galleryDialog" fullscreen>
      <div class="black-background">
        <v-carousel v-model="selectedImage" height="100%" hide-delimiters>
          <v-carousel-item v-for="(image, index) of room.images" :key="index">
            <v-row class="fill-height ma-0" align="center" justify="center">
              <v-img :src="`${image.url}`" max-height="100%" max-width="100%" contain></v-img>
            </v-row>
          </v-carousel-item>
          <div class="close-button">
            <v-btn @click="galleryDialog = false" text icon>
              <v-icon>close</v-icon>
            </v-btn>
          </div>
        </v-carousel>
      </div>
    </v-dialog>
  </v-container>
</template>

<script>
import SelectBed from '@/components/Roomdispositioner/Bed/SelectBed'
import RoomStats from '@/components/Roomdispositioner/Room/RoomStats'
import FileUpload from '@/components/Roomdispositioner/Room/FileUpload'

export default {
  name: 'SchowRoom',
  components: {
    SelectBed,
    RoomStats,
    FileUpload
  },
  data() {
    return {
      room: {},
      galleryDialog: false,
      selectedImage: null
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
    },
    addImages(images) {
      for (let image of images) {
        this.room.images.push(image)
      }
    },
    deleteImage(image) {
      this.$swal({
        title: 'Wirklich Löschen?',
        text: 'Willst du dieses Bild wirklich löschen?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ja, löschen!',
        cancelButtonText: 'Nein, abbrechen'
      }).then(result => {
        if (result.value) {
          this.axios
            .delete(`/images/${image.id}`)
            .then(response => {
              const index = this.room.images.indexOf(image)
              this.room.images.splice(index, 1)
            })
            .catch(() => {
              this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
            })
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.black-background {
  background-color: black;
  height: 100%;
  width: 100%;
}

.close-button {
  position: absolute;
  top: 0;
  right: 0;
}

.delete-icon {
  position: absolute;
  bottom: 0;
  right: 0;
  display: none;
}

.image-card:hover {
  .delete-icon {
    display: block;
  }
}
</style>
