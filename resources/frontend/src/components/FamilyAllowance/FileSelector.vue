<template>
  <div>
    <div class="d-flex align-center">
      <p class="mb-0 font-weight-bold mr-6">
        {{ label }}
      </p>
      <v-switch
        v-if="!disableToggle"
        v-model="isSubmitted"
        class="my-0 mr-6"
        :label="`${$t('Eingereicht')}: ${isSubmitted ? $t('Ja') : $t('Nein') }`"
        hide-details
      ></v-switch>
      <v-btn
        v-if="isSubmitted || disableToggle"
        text
        :loading="isLoading"
        class="center upload-button my-0"
        @click="$refs.fileInput.click()"
      >
        <v-icon x-large>
          add
        </v-icon>{{ $t('Datei hochladen') }}
      </v-btn>
      <input
        ref="fileInput"
        class="d-none"
        type="file"
        @change="uploadFieldChange"
      />

      <date-picker
        v-if="file && file.path"
        v-model="file.expiration_date"
        :label="$t('Verfallsdatum')"
        @input="changeExpirationDate"
      ></date-picker>
    </div>
  </div>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'

export default {
  components: {
    DatePicker
  },
  props: {
    disableToggle: {
      type: Boolean,
      default: false
    },
    disableExpirationDate: {
      type: Boolean,
      default: false
    },
    file: {
      type: Object,
      default: null
    },
    type: {
      type: String,
      required: true
    },
    label: {
      type: String,
      default: null
    },
    familyAllowanceId: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      isLoading: false
    }
  },

  computed: {
    isSubmitted: {
      get() {
        return this.file && this.file.is_submitted
      },
      set(value) {
        if (!this.file) {
          this.createFile({ is_submitted: value })
        } else {
          this.file.is_submitted = value
        }
      }
    }
  },
  methods: {
    uploadFieldChange(e) {
      const files = e.target.files || e.dataTransfer.files
      if (!files.length) return

      const formData = new FormData()
      formData.append('file', files[0])

      this.isLoading = true
      this.$store.commit('isSaving', true)
      this.axios
        .$post(`files/${this.file.id}/upload`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(({ data }) => {
          this.$emit('change', data)
        })
        .catch(() => {
          this.$store.dispatch('error', this.$t('Datei konnte nicht hochgeladen werden'))
        })
        .finally(() => {
          this.$store.commit('isSaving', false)
          this.isLoading = false
        })
      console.log('upload file')
    },
    createFile(props) {
      this.$store.commit('isSaving', true)
      this.axios.$post('files', {
        filable_id: this.familyAllowanceId,
        filable_type: 'App\\FamilyAllowance',
        type: this.type,
        ...props
      }).then(({ data }) => {
        this.$emit('add', data)
      }).catch(() => {
        this.$store.dispatch('error', this.$t('unbekannter-fehler'))
      }).finally(() => {
        this.$store.commit('isSaving', false)
      })
    },
    changeExpirationDate() {
      this.axios.$patch(`files/${this.file.id}`, {
        expiration_date: this.file.expiration_date
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Datei konnte nicht hochgeladen werden'))
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.upload-button {
  // margin
}
</style>
