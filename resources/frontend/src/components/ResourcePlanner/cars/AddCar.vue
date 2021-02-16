<template>
  <card-layout
    :saving="isSaving"
    :title="$t('Auto hinzufügen')"
    @save="save"
    @cancel="$emit('input', false)"
  >
    <template>
      <car-form
        ref="form"
        v-model="car"
        @submit="save"
      >
      </car-form>
      <!-- <v-row>
        <v-col
          cols="12"
          md="6"
        >
          <select-images
            v-model="car.image"
            single-file
          ></select-images>
        </v-col>
      </v-row> -->
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import CarForm from '@/components/forms/CarForm'

export default {
  naem: 'AddInventar',
  components: {
    CardLayout,
    CarForm
  },
  props: {
    value: Boolean
  },
  data() {
    return {
      car: {},
      isSaving: false
    }
  },
  watch: {
    value() {
      if (!this.value) {
        this.$refs.form.reset()
      }
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isSaving = true
        this.$store.dispatch('createCar', this.car).then(() => {
          this.$refs.form.reset()
          this.$emit('input', false)
          this.$emit('add')
        }).catch(() => {
          this.$swal('Fehler', this.$t('Auto konnte nicht erstellt werden'), 'error')
        }).finally(() => {
          this.isSaving = false
        })
      }
    }
  }
}
</script>
