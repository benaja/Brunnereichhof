<template>
  <v-expansion-panels
    v-if="value"
    class="my-10"
  >
    <v-expansion-panel
      :readonly="!$auth.user().hasPermission('superadmin', 'employee_read')"
    >
      <v-expansion-panel-header hide-actions>
        <h3>{{ $t('Familienzulagen') }}</h3>
      </v-expansion-panel-header>
      <v-expansion-panel-content>
        <file-selector
          type="id"
          :file="fileByType('id')"
          :label="$t('ID/Pass')"
          :family-allowance-id="value.id"
          @add="addFile"
          @change="updateFile"
        ></file-selector>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
import FileSelector from './FileSelector'

export default {
  components: {
    FileSelector
  },
  props: {
    value: {
      type: Object,
      default: null
    },
    parent: {
      type: Object,
      default: null
    },
    model: {
      type: String,
      required: true
    }
  },
  methods: {
    fileByType(type) {
      return this.value.files.find(f => f.type === type)
    },
    updateFile(file) {
      const originalFile = this.files.find(f => f.type === file.type)
      const index = this.files.indexOf(originalFile)
      this.value.files.splice(index, 1)

      this.value.files = [...this.value.files]
    },
    addFile(file) {
      this.value.files.push(file)
      this.value.files = [...this.value.files]
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
