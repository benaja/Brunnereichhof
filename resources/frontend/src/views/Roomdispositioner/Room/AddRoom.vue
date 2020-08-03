<template>
  <fragment>
    <navigation-bar title="Raum erstellen"></navigation-bar>
    <v-container>
      <room-form
        ref="form"
        v-model="room"
      ></room-form>
      <select-images v-model="room.images"></select-images>
      <div class="mt-5">
        <v-btn
          text
          to="/rooms"
          color="black"
        >
          Abbrechen
        </v-btn>
        <v-btn
          depressed
          dark
          color="blue"
          class="float-right"
          :loading="isLoading"
          @click="save"
        >
          Speichern
        </v-btn>
      </div>
    </v-container>
  </fragment>
</template>

<script>
import SelectImages from '@/components/Roomdispositioner/Room/SelectImages'
import { rules } from '@/utils'
import RoomForm from '@/components/forms/RoomForm'

export default {
  components: {
    SelectImages,
    RoomForm
  },
  data() {
    return {
      room: {
        beds: [],
        images: [],
        isActive: 1
      },
      rules,
      isLoading: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        const formData = new FormData()
        for (const image of this.room.images) {
          formData.append('images[]', image)
        }
        formData.append('name', this.room.name)
        formData.append('location', this.room.location)
        formData.append('number', this.room.number)
        formData.append('comment', this.room.comment || '')
        formData.append('isActive', this.room.isActive)
        formData.append('beds', JSON.stringify(this.room.beds))
        const config = {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: progressEvent => {
            this.percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
          }
        }
        this.axios
          .post('/rooms', formData, config)
          .then(() => {
            this.$router.push('/rooms')
          })
          .catch(() => {
            this.$swal('Fehler', 'Raum konnte nicht gespeichert werden.', 'error')
          })
          .finally(() => {
            this.isLoading = false
          })
      }
    }
  }
}
</script>
