<template>
  <div class="my-2">
    <div class="d-flex align-center">
      <p class="mb-0 font-weight-bold mr-6">
        {{ label }}
      </p>
      <v-switch
        v-if="!disableSubmitted"
        v-model="isSubmitted"
        class="my-0 mr-6"
        :label="submittedLabel"
        hide-details
        :readonly="readonly"
      ></v-switch>
      <template v-if="isSubmitted || disableSubmitted">
        <template v-if="isFileSelected">
          <v-btn
            text
            color="primary"
            :href="file.url"
            target="blank"
          >
            {{ $t('Datei öffnen') }}
          </v-btn>
          <v-btn
            text
            :disabled="readonly"
            @click="removeFile"
          >
            <v-icon class="mr-1">
              delete
            </v-icon>
            {{ $t('Datei löschen') }}
          </v-btn>
        </template>
        <v-btn
          v-else
          :disabled="readonly"
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
          :disabled="readonly"
          class="d-none"
          type="file"
          @change="uploadFieldChange"
        />

        <date-picker
          v-if="isFileSelected"
          v-model="file.expiration_date"
          :readonly="readonly"
          :label="$t('Verfallsdatum')"
          @input="update"
        ></date-picker>
      </template>
    </div>
  </div>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import { confirmAction } from '@/utils'
import i18n from '@/plugins/i18n'

export default {
  components: {
    DatePicker
  },
  props: {
    disableSubmitted: {
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
    submittedLabel: {
      type: String,
      default: i18n.tc('Eingereicht')
    },
    filableId: {
      type: Number,
      required: true
    },
    filableType: {
      type: String,
      default: 'App\\FamilyAllowance'
    },
    readonly: {
      type: Boolean,
      default: null
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
      return this.file && this.file.path
    }
  },

  mounted() {
    if (!this.submittedLabel) {
      this.submittedLabel = this.$t('Eingereicht')
    }
  },

  methods: {
    async uploadFieldChange(e) {
      const files = e.target.files || e.dataTransfer.files
      if (!files.length) return

      if (!this.file) {
        await this.createFile()
      }

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
    async createFile(props) {
      return new Promise(resolve => {
        this.$store.commit('isSaving', true)
        this.axios.$post('files', {
          filable_id: this.filableId,
          filable_type: this.filableType,
          type: this.type,
          ...props
        }).then(({ data }) => {
          this.$emit('add', data)
        }).catch(() => {
          this.$store.dispatch('error', this.$t('unbekannter-fehler'))
        }).finally(() => {
          this.$store.commit('isSaving', false)

          this.$nextTick(() => resolve())
        })
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
