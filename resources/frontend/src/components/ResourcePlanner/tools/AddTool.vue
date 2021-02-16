<template>
  <card-layout
    :saving="isSaving"
    :title="$t('Werkzeug hinzufugen')"
    @save="save"
    @cancel="$emit('input', false)"
  >
    <template>
      <tool-form
        ref="form"
        v-model="tool"
        @submit="save"
      >
      </tool-form>
    </template>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import ToolForm from '@/components/forms/ToolForm'

export default {
  naem: 'AddInventar',
  components: {
    CardLayout,
    ToolForm
  },
  props: {
    value: Boolean
  },
  data() {
    return {
      tool: {},
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
        this.$store.dispatch('createTool', this.tool).then(() => {
          this.$refs.form.reset()
          this.$emit('input', false)
          this.$emit('add')
        }).catch(() => {
          this.$swal('Fehler', this.$t('Werkzeug konnte nicht erstellt werden'), 'error')
        }).finally(() => {
          this.isSaving = false
        })
      }
    }
  }
}
</script>
