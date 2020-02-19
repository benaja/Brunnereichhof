<template>
  <v-col class="d-flex child-flex" cols="4">
    <div class="upload-container">
      <div class="file-upload">
        <p class="text-center file-upload-text">
          <v-progress-circular v-if="isLoading" indeterminate color="grey lighten-5"></v-progress-circular>
          <v-btn v-else text x-large class="center upload-button" @click="$refs.fileInput.click()">
            <v-icon x-large>add</v-icon>Bild hinzufügen
          </v-btn>
        </p>
        <input
          type="file"
          multiple="multiple"
          id="attachments"
          ref="fileInput"
          @change="uploadFieldChange"
        />
      </div>
    </div>
  </v-col>
</template>

<script>
export default {
  name: 'FileUpload',
  props: ['settings'],
  data() {
    return {
      attachments: [],
      data: new FormData(),
      percentCompleted: 0,
      isLoading: false
    }
  },
  watch: {},
  computed: {},
  methods: {
    validate() {
      if (!this.attachments.length) {
        return false
      }
      return true
    },
    prepareFields() {
      this.data = new FormData()
      for (let i = this.attachments.length - 1; i >= 0; i--) {
        this.data.append('images[]', this.attachments[i])
      }
    },
    uploadFieldChange(e) {
      let files = e.target.files || e.dataTransfer.files
      if (!files.length) {
        return
      }
      for (let i = files.length - 1; i >= 0; i--) {
        this.attachments.push(files[i])
      }
      this.submit()
    },
    submit() {
      this.prepareFields()
      if (!this.validate()) {
        return false
      }
      let config = {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: progressEvent => {
          this.percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      }
      this.isLoading = true
      this.axios
        .post(`/rooms/${this.$route.params.id}/images`, this.data, config)
        .then(response => {
          this.$emit('uploaded', response.data)
          this.resetData()
          this.isLoading = false
        })
        .catch(() => {
          this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
        })
    },
    resetData() {
      this.data = new FormData()
      this.attachments = []
    }
  }
}
</script>

<style lang="scss" scoped>
#attachments {
  display: none;
}

.file-upload-text {
  margin-top: calc(50% - 30px);
}

.upload-container {
  padding-bottom: 100%;
  position: relative;
}

.file-upload {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background-color: rgb(236, 236, 236);
}
</style>
