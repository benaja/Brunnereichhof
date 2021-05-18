<template>
  <div class="child">
    <v-row>
      <v-col
        cols="12"
        md="4"
      >
        <v-text-field
          v-model="child.firstname"
          :label="$t('Vorname')"
          hide-details
          @input="update"
        ></v-text-field>
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <v-text-field
          v-model="child.lastname"
          :label="$t('Nachname')"
          hide-details
          @input="update"
        ></v-text-field>
      </v-col>
      <v-col
        cols="12"
        md="4"
      >
        <date-picker
          v-model="child.birthdate"
          :label="$t('Geburtstag')"
          @input="update"
        ></date-picker>
      </v-col>
    </v-row>
    <file-selector
      type="birth_document"
      :file="fileByType('birth_document')"
      :label="$t('Geburtsurkunde')"
      :submitted-label="$t('Eingereicht')"
      :filable-id="child.id"
      filable-type="App\\Child"
      @add="addFile"
      @change="updateFile"
    ></file-selector>
    <file-selector
      type="school_confirmation"
      :file="fileByType('school_confirmation')"
      :label="$t('Schulbestätigung')"
      :submitted-label="$t('Eingereicht')"
      :filable-id="child.id"
      filable-type="App\\Child"
      @add="addFile"
      @change="updateFile"
    ></file-selector>

    <p class="text-right">
      <v-btn
        color="red"
        text
        class="mt-3"
        @click="deleteChild"
      >
        {{ $t('Kind löschen') }} <v-icon class="ml-1">
          delete
        </v-icon>
      </v-btn>
    </p>
  </div>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import { confirmAction } from '@/utils'
import FileSelector from './FileSelector'

export default {
  components: {
    DatePicker,
    FileSelector
  },
  props: {
    child: {
      type: Object,
      required: true
    }
  },
  computed: {
    update() {
      return this._.debounce(() => {
        this.axios.$patch(`children/${this.child.id}`, this.child).catch(() => {
          this.$store.dispatch('error', 'Es ist ein unerwarteter Fehler aufgetreten')
        })
      }, 300)
    }
  },
  methods: {
    deleteChild() {
      confirmAction().then(value => {
        if (value) {
          this.axios.$delete(`children/${this.child.id}`)
            .then(() => {
              this.$emit('remove', this.child)
            }).catch(() => {
              this.$store.dispatch('error', 'Es ist ein unerwarteter Fehler aufgetreten')
            })
        }
      })
    },
    fileByType(type) {
      return this.child.files.find(f => f.type === type)
    },
    updateFile(file) {
      const originalFile = this.child.files.find(f => f.type === file.type)
      const index = this.child.files.indexOf(originalFile)

      this.child.files[index] = file

      this.child.files = [...this.child.files]
    },
    addFile(file) {
      this.child.files.push(file)
      this.child.files = [...this.child.files]
    }
  }
}
</script>

<style lang="scss" scoped>
.child {
  background-color: white;
  border-radius: 5px;
  padding: 5px 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.104);

  margin: 10px 0;
}
</style>
