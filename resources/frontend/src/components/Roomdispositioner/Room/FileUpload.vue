<template>
  <v-col
    class="d-flex child-flex"
    :cols="multiple ? 4 : 12"
  >
    <div class="upload-container">
      <div class="file-upload">
        <p class="text-center file-upload-text">
          <v-progress-circular
            v-if="isLoading"
            indeterminate
            color="grey lighten-5"
          ></v-progress-circular>
          <v-btn
            v-else
            text
            x-large
            class="center upload-button"
            @click="$refs.fileInput.click()"
          >
            <v-icon x-large>
              add
            </v-icon>Bild hinzufügen
          </v-btn>
        </p>
        <input
          id="attachments"
          ref="fileInput"
          type="file"
          :multiple="multiple"
          @change="uploadFieldChange"
        />
      </div>
    </div>
  </v-col>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      default: null
    },
    uploadOnChange: {
      type: Boolean,
      default: false
    },
    uploadUrl: {
      type: String,
      default: null
    },
    multiple: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      data: new FormData(),
      percentCompleted: 0,
      isLoading: false
    }
  },
  methods: {
    uploadFieldChange(e) {
      const files = e.target.files || e.dataTransfer.files
      if (!files.length) return
      if (this.multiple) {
        for (let i = 0; i < files.length; i++) {
          this.data.append('images[]', files[i])
        }
      } else {
        this.data.append('image', files[0])
      }
      if (this.uploadOnChange) {
        this.uploadFiles()
      } else if (this.multiple) {
        this.$emit('input', files)
      } else {
        this.$emit('input', files[0])
      }
    },
    uploadFiles() {
      const config = {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: progressEvent => {
          this.percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      }
      this.isLoading = true
      this.$store.commit('isSaving', true)
      this.axios
        .post(this.uploadUrl, this.data, config)
        .then(response => {
          this.$emit('uploaded', response.data)
          this.data = new FormData()
        })
        .catch(() => {
          this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
        })
        .finally(() => {
          this.isLoading = false
          this.$store.commit('isSaving', false)
        })
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
