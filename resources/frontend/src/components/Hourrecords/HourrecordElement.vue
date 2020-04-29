<template>
  <v-row>
    <v-col
      cols="12"
      sm="2"
      class="py-0"
    >
      <v-text-field
        v-if="editMode"
        v-model="value.hours"
        :rules="[rules.required]"
        type="number"
        label="Stunden*"
        class="pa-1 ma-0"
        @change="update"
      />
      <p
        v-else
        class="body-2"
      >
        <span class="body-1">Stunden:</span>
        {{ value.hours }}
      </p>
    </v-col>
    <v-col
      cols="12"
      sm="4"
      class="py-0"
    >
      <v-combobox
        v-if="editMode"
        v-model="value.culture"
        :items="cultures"
        :rules="[rules.required]"
        class="pa-1 ma-0"
        item-text="name"
        item-value="id"
        label="Kultur/Arbeit*"
        autocomplete="off"
        @input="update"
      />
      <p
        v-else
        class="body-2"
      >
        <span class="body-1">Kultur:</span>
        {{ value.culture ? value.culture.name : '' }}
      </p>
    </v-col>
    <v-col
      cols="12"
      :sm="commendWidth"
      class="py-0"
    >
      <v-textarea
        v-if="editMode"
        v-model="value.comment"
        label="Kommentar"
        class="pa-1 ma-0"
        rows="1"
        auto-grow
        @change="update"
      />
      <p
        v-else
        class="body-2"
      >
        <span class="body-1">Kommentar:</span>
        {{ value.comment }}
      </p>
    </v-col>
    <v-col
      v-if="editMode"
      cols="12"
      sm="1"
      class="py-0"
      align-self="center"
    >
      <v-btn
        text
        icon
        color="red"
        class="mb-5"
        @click="deleteHourrecord"
      >
        <v-icon>delete</v-icon>
      </v-btn>
    </v-col>
    <v-col
      v-if="!editMode && value.createdByAdmin"
      cols="12"
      sm="2"
      class="py-0 mb-4 mb-md-auto"
    >
      <p class="text-left text-sm-right grey--text pb-0">
        Von Admin erstellt
      </p>
    </v-col>
  </v-row>
</template>

<script>
import { rules } from '@/utils'

export default {
  name: 'HourrecrodElement',
  props: {
    value: {
      type: Object,
      default: null
    },
    editMode: Boolean,
    cultures: {
      type: Array,
      default: null
    },
    adminMode: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      rules
    }
  },
  computed: {
    commendWidth() {
      if (this.editMode) return 5
      if (this.adminMode) return 4
      return 6
    }
  },
  methods: {
    update() {
      this.axios
        .put(`/hourrecord/${this.value.id}`, this.value)
        .then((response) => {
          this.$emit('input', response.data)
        })
        .catch(() => {
          this.$swal('Fehler', 'Ein unbekannter Fehler ist aufgetreten. Versuchen Sie es bitte später erneut.', 'error')
        })
    },
    deleteHourrecord() {
      this.axios
        .delete(`/hourrecord/${this.value.id}`)
        .then(() => {
          this.$emit('remove')
        })
        .catch(() => {
          this.$swal('Fehler', 'Element konnte aus einem unbekannten Grund nicht gelöscht werden. Bitter versuchen Sie es später erneut', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
