<template>
  <v-form @submit="save" ref="form">
    <v-text-field label="Name" v-model="value.name_de" :rules="[rules.required]"></v-text-field>
    <v-checkbox v-model="value.manually" label="Stunden manuell eingeben"></v-checkbox>
    <h4>Erfassungsarten</h4>
    <p v-if="value.work_input_types.length === 0">Noch keine Erfassungsart hinzugefügt</p>
    <v-row>
      <template v-for="(inputType, index) of value.work_input_types">
        <v-col cols="12" sm="8" :key="`${index}-name`" class="py-0">
          <v-text-field v-model="inputType.name" label="Name" :rules="[rules.required]"></v-text-field>
        </v-col>
        <v-col cols="10" sm="3" :key="`${index}-hours`" class="py-0">
          <v-text-field
            v-model="inputType.hours"
            label="Stunden"
            type="hours"
            :rules="[rules.required, rules.maxHeight, rules.minHeight]"
          ></v-text-field>
        </v-col>
        <v-col cols="2" sm="1" :key="`${index}-delete`" class="py-0">
          <v-btn icon color="red" class="float-right mt-4" @click="removeWorkInputType(index)">
            <v-icon>delete</v-icon>
          </v-btn>
        </v-col>
      </template>
    </v-row>
    <v-btn color="primary" class="mt-3" outlined @click="addInputType">Erfassungsart hinzufügen</v-btn>
    <v-btn
      color="primary"
      class="mt-3 float-right"
      depressed
      @click="save"
      :loading="isLoading"
    >Speichern</v-btn>
  </v-form>
</template>

<script>
import { rules } from '@/utils'

export default {
  props: {
    value: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      rules: {
        ...rules,
        maxHeight: v => v <= 24 || 'Maximal 24 Stunden',
        minHeight: v => v > 0 || 'Müssen mehr als 0 Studen sein'
      },
      isLoading: false
    }
  },
  methods: {
    addInputType() {
      this.value.work_input_types.push({
        name: '',
        hours: null
      })
    },
    save() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        this.axios
          .put(`/worktypes/${this.value.id}`, this.value)
          .then(() => {
            this.$store.dispatch('alert', {
              text: 'Erfolgreich gespeichert'
            })
          })
          .catch(() => {
            this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
          })
          .finally(() => {
            this.isLoading = false
          })
      }
    },
    removeWorkInputType(index) {
      this.value.work_input_types.splice(index, 1)
    }
  }
}
</script>

<style></style>
