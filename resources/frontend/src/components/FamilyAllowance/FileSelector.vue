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
      <template v-if="isFileSelected">
        <v-btn
          v-if="isFileSelected"
          text
          color="primary"
          :href="file.url"
          target="blank"
        >
          {{ $t('Datei öffnen') }}
        </v-btn>
        <v-btn
          text
          @click="removeFile"
        >
          <v-icon class="mr-1">
            delete
          </v-icon>
          {{ $t('Datei löschen') }}
        </v-btn>
      </template>
      <v-btn
        v-if="!isFileSelected && isSubmitted"
        text
        :loading="isLoading"
        class="my-0"
        @click="$refs.fileInput.click()"
      >
        <v-icon class="mr-1">
          add
        </v-icon>
        {{ $t('Datei hochladen') }}
      </v-btn>
      <input
        ref="fileInput"
        class="d-none"
        type="file"
        @change="uploadFieldChange"
      />

      <date-picker
        v-if="isFileSelected"
        v-model="file.expiration_date"
        :label="$t('Verfallsdatum')"
        @input="update"
      ></date-picker>
    </div>
  </div>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import { confirmAction } from '@/utils'

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
          this.update()
        }
      }
    },
    isFileSelected() {
      return (this.isSubmitted || this.disableToggle) && this.file.path
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
    update() {
      this.$store.commit('isSaving', true)
      this.axios.$patch(`files/${this.file.id}`, {
        expiration_date: this.file.expiration_date,
        is_submitted: this.file.is_submitted,
        path: this.file.path
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Datei konnte nicht hochgeladen werden'))
      }).finally(() => {
        this.$store.commit('isSaving', false)
      })
    },
    removeFile() {
      confirmAction(this.$t('Willst du die Datei wirklich löschen?')).then(value => {
        if (value) {
          this.file.path = null
          this.update()
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
