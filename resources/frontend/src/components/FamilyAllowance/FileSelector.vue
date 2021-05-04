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
    parentId: {
      type: Number,
      required: true
    },
    parentModel: {
      type: String,
      required: true
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
    uploadFieldChange() {
      console.log('upload file')
    },
    createFile(props) {
      this.axios.$post('files', {
        filable_id: this.parentId,
        filable_type: this.parentModel,
        type: this.type,
        ...props
      }).then(({ data }) => {
        this.$emit('add', data)
      }).catch(() => {
        this.$store.dispatch('error', this.$t('unbekannter-fehler'))
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
