<template>
  <card-layout
    v-if="car"
    :saving="isSaving"
    :title="$t('Auto bearbeiten')"
    @save="save"
    @cancel="$emit('close')"
  >
    <car-form
      v-if="car"
      ref="form"
      v-model="car"
    >
    </car-form>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import CarForm from '@/components/forms/CarForm'

export default {
  components: {
    CardLayout,
    CarForm
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      isSaving: false,
      car: this._.cloneDeep(this.value)
    }
  },
  watch: {
    value() {
      this.car = this._.cloneDeep(this.value)
    }
  },
  methods: {
    save() {
      this.isSaving = true
      this.$store.dispatch('updateCar', this.car).then(() => {
        this.$emit('close')
      }).catch(() => {
        this.$store.commit('error', this.$t('Fehler beim Speichern'))
      }).finally(() => {
        this.isSaving = false
      })
    }
  }
}
</script>
