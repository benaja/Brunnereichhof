<template>
  <v-row>
    <v-col
      v-for="(image, index) of filesToDisplay"
      :key="image.id"
      class="d-flex child-flex"
      :cols="singleFile ? 12 : 4"
    >
      <v-card
        flat
        tile
        class="d-flex image-card"
      >
        <v-img
          :src="image.url"
          aspect-ratio="1"
          class="grey lighten-2"
          @click="selectImage(index)"
        >
          <template v-slot:placeholder>
            <v-row
              class="fill-height ma-0"
              align="center"
              justify="center"
            >
              <v-progress-circular
                indeterminate
                color="grey lighten-5"
              ></v-progress-circular>
            </v-row>
          </template>
        </v-img>
        <v-btn
          v-if="!disabled"
          fab
          dark
          color="red"
          class="delete-icon"
          @click="deleteImage(image)"
        >
          <v-icon>delete</v-icon>
        </v-btn>
      </v-card>
    </v-col>
    <file-upload
      v-if="!disabled && (!singleFile || !filesToDisplay.length)"
      :upload-on-change="uploadOnChange"
      :upload-url="uploadUrl"
      :multiple="!singleFile"
      @input="displayImages"
      @uploaded="addImages"
    ></file-upload>
    <v-dialog
      v-model="galleryDialog"
      fullscreen
    >
      <div class="black-background">
        <v-carousel
          v-model="selectedImage"
          height="100%"
          hide-delimiters
        >
          <v-carousel-item
            v-for="(image, index) of filesToDisplay"
            :key="index"
          >
            <v-row
              class="fill-height ma-0"
              align="center"
              justify="center"
            >
              <v-img
                :src="`${image.url}`"
                max-height="100%"
                max-width="100%"
                contain
              ></v-img>
            </v-row>
          </v-carousel-item>
          <div class="close-button">
            <v-btn
              text
              icon
              @click="galleryDialog = false"
            >
              <v-icon>close</v-icon>
            </v-btn>
          </div>
        </v-carousel>
      </div>
    </v-dialog>
  </v-row>
</template>

<script>
import FileUpload from '@/components/Roomdispositioner/Room/FileUpload'
import { confirmAction } from '@/utils'

export default {
  components: {
    FileUpload
  },
  props: {
    value: {
      type: [Array, String, File],
      default: () => []
    },
    uploadOnChange: {
      type: Boolean,
      default: false
    },
    uploadUrl: {
      type: String,
      default: null
    },
    disabled: {
      type: Boolean,
      default: false
    },
    singleFile: {
      type: Boolean,
      default: false
    },
    delete: {
      type: Function,
      default: () => {}
    },
    imageUrl: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedImage: null,
      galleryDialog: false,
      imageUrls: this.imageUrl ? [this.imageUrl] : []
    }
  },
  computed: {
    filesToDisplay() {
      if (!this.uploadOnChange) {
        return this.imageUrls.map((image, index) => ({
          id: index,
          url: image
        }))
      }
      if (this.singleFile) {
        if (this.value && this.value.length) {
          return [{
            id: 0,
            url: this.value
          }]
        }
        return []
      }
      return this.value
    }
  },
  watch: {
    value() {
      if (!this.value || !this.value.length) {
        this.imageUrls = []
      }
    }
  },
  methods: {
    selectImage(index) {
      this.selectedImage = index
      this.galleryDialog = true
    },
    addImages(newImages) {
      if (this.singleFile) {
        this.$emit('input', newImages)
      } else {
        const images = [...this.value]
        for (const image of newImages) {
          images.push(image)
        }
        this.$emit('input', images)
      }
    },
    deleteImage(image) {
      if (this.uploadOnChange) {
        confirmAction('Willst du dieses Bild wirklich löschen?').then(value => {
          if (value) {
            this.$store.commit('isSaving', true)
            this.delete(image).then(() => {
              this.spliceImage(image)
            }).catch(() => {
              this.$store.dispatch('error', 'Bild konnte nicht gelöscht werden')
            }).finally(() => {
              this.$store.commit('isSaving', false)
            })
          }
        })
      } else {
        const index = this.imageUrls.indexOf(image.url)
        this.imageUrls.splice(index, 1)
        if (Array.isArray(this.value)) {
          this.value.splice(index, 1)
        } else {
          this.$emit('input', null)
        }
      }
    },
    spliceImage(image) {
      if (this.singleFile) {
        this.$emit('input', null)
      } else {
        const index = this.value.indexOf(image)
        const newImages = [...this.value]
        newImages.splice(index, 1)
        this.$emit('input', newImages)
      }
    },
    displayImages(files) {
      if (this.singleFile) {
        this.$emit('input', files)
        this.readFile(files)
      } else {
        for (const file of files) {
          this.value.push(file)
          this.$emit('input', this.value)
          this.readFile(file)
        }
      }
    },
    readFile(file) {
      const reader = new FileReader()
      reader.onload = e => {
        this.imageUrls.push(e.target.result)
      }
      reader.readAsDataURL(file)
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
