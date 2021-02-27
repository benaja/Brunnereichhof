<template>
  <draggable
    :value="internalValue"
    :data-customer-id="customerId"
    group="tools"
    class="elevation-1"
    @add="add"
    @remove="remove"
  >
    <tool-card
      v-for="tool of internalValue"
      :key="tool.id"
      :tool="tool"
    ></tool-card>
  </draggable>
</template>

<script>
import ToolCard from '@/components/ResourcePlanner/plan/ToolCard'
import Draggable from 'vuedraggable'
import { confirmAction } from '@/utils'

export default {
  components: {
    ToolCard,
    Draggable
  },
  props: {
    value: {
      type: Array,
      default: () => []
    },
    customerId: {
      type: Number,
      default: null
    },
    availableTools: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      internalValue: this.value
    }
  },
  watch: {
    internalValue() {
      this.$emit('input', this.internalValue)
    },
    value() {
      this.internalValue = this.value
    }
  },
  methods: {
    add(value) {
      const { toolId } = value.item.dataset

      const alreadyExists = this.value.find(t => t.id === Number(toolId))
      if (alreadyExists && this.customerId) {
        this.$store.dispatch('alert', { type: 'warning', text: this.$t('Werkzeug bereits vorhanden') })
        return
      }

      const free = this.availableTools.find(t => t.id === Number(toolId))
      const toCustomerId = value.to.dataset.customerId
      if (!free && toCustomerId) {
        confirmAction({
          title: this.$t('Werkzeug aufgebraucht'),
          text: this.$t('Dieses Werkzeug wird bereits bei anderen Kunden verwendet. Möchtest du es trotzdem zu diesem Kunden hinzufügen?'),
          confirmButtonText: this.$t('Ja, hinzufügen'),
          cancelButtonText: this.$t('Nein'),
          showCancelButton: true,
          icon: 'warning'
        }).then(result => {
          if (result.value) {
            this.$emit('add', toolId)
          }
        })
      } else {
        this.$emit('add', toolId)
      }
    },
    remove(value) {
      const toCustomerId = value.to.dataset.customerId
      const { toolId } = value.item.dataset

      if (!toCustomerId) {
        this.$emit('remove', toolId)
      }
    }
  }
}
</script>

<style>

</style>
