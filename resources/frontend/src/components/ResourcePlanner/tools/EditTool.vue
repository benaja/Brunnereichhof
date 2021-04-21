<template>
  <card-layout
    v-if="tool"
    :saving="isSaving"
    :title="$t('Werkzeug bearbeiten')"
    @save="save"
    @cancel="$emit('close')"
  >
    <tool-form
      v-if="tool"
      ref="form"
      v-model="tool"
    >
    </tool-form>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import ToolForm from '@/components/forms/ToolForm'

export default {
  components: {
    CardLayout,
    ToolForm
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
      tool: this._.cloneDeep(this.value)
    }
  },
  watch: {
    value() {
      this.tool = this._.cloneDeep(this.value)
    }
  },
  methods: {
    save() {
      this.isSaving = true
      this.$store.dispatch('updateTool', this.tool).then(() => {
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
