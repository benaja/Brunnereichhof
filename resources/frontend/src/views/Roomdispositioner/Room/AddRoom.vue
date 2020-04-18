<template>
  <v-container>
    <h1>Raum erstellen</h1>
    <v-form lazy-validation ref="form">
      <v-row>
        <v-col cols="12" md="6" class="py-0">
          <v-text-field label="Name" v-model="room.name" :rules="rules.required" color="blue"></v-text-field>
        </v-col>
        <v-col cols="12" md="6" class="py-0">
          <v-text-field
            label="Nummer"
            type="number"
            v-model="room.number"
            :rules="rules.required"
            color="blue"
          ></v-text-field>
        </v-col>
        <v-col cols="12" class="py-0">
          <v-text-field
            label="Standort"
            v-model="room.location"
            :rules="rules.required"
            color="blue"
          ></v-text-field>
        </v-col>
        <v-col cols="12" class="py-0">
          <v-textarea label="Kommentar" v-model="room.comment" rows="1" auto-grow color="blue"></v-textarea>
        </v-col>
        <v-col cols="12" class="px-4">
          <select-bed v-model="room.beds"></select-bed>
        </v-col>
        <v-col cols="12" class="py-0">
          <select-images v-model="room.images"></select-images>
        </v-col>
        <v-col cols="12" class="mt-4">
          <v-btn text>Abbrechen</v-btn>
          <v-btn text color="blue" class="float-right" @click="save" :loading="isLoading">Speichern</v-btn>
        </v-col>
      </v-row>
    </v-form>
  </v-container>
</template>

<script>
import SelectBed from '@/components/Roomdispositioner/Bed/SelectBed'
import SelectImages from '@/components/Roomdispositioner/Room/SelectImages'

export default {
  components: {
    SelectBed,
    SelectImages
  },
  data() {
    return {
      room: {
        beds: [],
        images: []
      },
      rules: {
        required: [v => !!v || 'Dieses Feld muss vorhanden sein']
      },
      isLoading: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        const formData = new FormData()
        for (let image of this.room.images) {
          formData.append('images[]', image)
        }
        formData.append('name', this.room.name)
        formData.append('location', this.room.location)
        formData.append('number', this.room.number)
        formData.append('comment', this.room.comment)
        formData.append('beds', JSON.stringify(this.room.beds))
        const config = {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: progressEvent => {
            this.percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
          }
        }
        this.axios
          .post('/rooms', formData, config)
          .then(response => {
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
