<template>
  <v-row>
    <v-col cols="12" sm="2">
      <v-text-field
        v-if="editMode"
        type="number"
        v-model="value.hours"
        label="Stunden"
        class="pa-1 ma-0"
        @change="update"
      />
      <p v-else class="body-2">
        <span class="body-1">Stunden:</span>
        {{value.hours}}
      </p>
    </v-col>
    <v-col cols="12" sm="4">
      <v-combobox
        v-if="editMode"
        :items="cultures"
        v-model="value.culture"
        class="pa-1 ma-0"
        item-text="name"
        item-value="id"
        label="Kultur/Arbeit"
        @input="update"
        autocomplete="off"
      />
      <p v-else class="body-2">
        <span class="body-1">Kultur:</span>
        {{value.culture ? value.culture.name : ''}}
      </p>
    </v-col>
    <v-col cols="12" sm="4">
      <v-textarea
        v-if="editMode"
        v-model="value.comment"
        label="Kommentar"
        class="pa-1 ma-0"
        rows="1"
        auto-grow
        @change="update"
      />
      <p v-else class="body-2">
        <span class="body-1">Kommentar:</span>
        {{value.comment}}
      </p>
    </v-col>
    <v-col cols="12" sm="1" offset-sm="1" v-if="editMode">
      <p class="text-center">
        <v-btn text icon color="red" @click="deleteHourrecord">
          <v-icon>delete</v-icon>
        </v-btn>
      </p>
    </v-col>
    <v-col cols="12" sm="2" v-if="!editMode && value.createdByAdmin">
      <p class="text-left text-sm-right grey--text pb-0">Von Admin erstellt</p>
    </v-col>
    <v-col cols="12" class="hidden-sm-and-up mt-4"></v-col>
  </v-row>
</template>

<script>
export default {
  name: 'HourrecrodElement',
  props: {
    value: Object,
    editMode: Boolean,
    cultures: Array
  },
  methods: {
    update() {
      this.axios
        .put(process.env.VUE_APP_API_URL + 'hourrecord/' + this.value.id, this.value)
        .then(response => {
          this.$emit('input', response.data)
        })
        .catch(() => {
          this.$swal('Fehler', 'Ein unbekannter Fehler ist aufgetreten. Versuchen Sie es bitte später erneut.', 'error')
        })
    },
    deleteHourrecord() {
      this.axios
        .delete(process.env.VUE_APP_API_URL + 'hourrecord/' + this.value.id)
        .then(() => {
          this.$emit('remove')
        })
        .catch(() => {
          this.$swal('Fehler', 'Element konnte aus einem unbekannten Grund nicht gelöscht werden.', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
