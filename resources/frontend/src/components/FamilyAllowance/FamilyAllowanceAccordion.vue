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
          disable-submitted
          @add="addFile"
          @change="updateFile"
        ></file-selector>
        <file-selector
          type="foreign_id"
          :file="fileByType('foreign_id')"
          :label="$t('Ausländerausweis')"
          :family-allowance-id="value.id"
          disable-submitted
          @add="addFile"
          @change="updateFile"
        ></file-selector>

        <v-select
          v-model="value.civil_status"
          :label="$t('Zivilstand')"
          :items="civilStates"
          @input="update('civil_status')"
        ></v-select>

        <v-switch
          v-model="value.has_family_allowance"
          :label="$t('Bansprucht Familienzulagen')"
          @change="update('has_family_allowance')"
        ></v-switch>

        <template v-if="value.has_family_allowance">
          <DatePicker
            v-model="value.expiration_of_family_allowance"
            :label="$t('Verfallsdatum Familienzulagen')"
            @input="update('expiration_of_family_allowance')"
          ></DatePicker>

          <v-switch
            v-model="value.partner_employed"
            :label="$t('Partner erwerbstätig')"
            @change="update('partner_employed')"
          ></v-switch>

          <v-switch
            v-model="value.needs_e411_form"
            :label="$t('Benötigt Formular E411')"
            @change="update('needs_e411_form')"
          ></v-switch>

          <v-switch
            v-model="value.is_e411_handed_out"
            :label="$t('E411 ausgeteilt')"
            @change="update('is_e411_handed_out')"
          ></v-switch>

          <file-selector
            type="e411"
            :file="fileByType('e411')"
            :label="$t('E411')"
            :family-allowance-id="value.id"
            @add="addFile"
            @change="updateFile"
          ></file-selector>

          <file-selector
            type="marriage_document"
            :file="fileByType('marriage_document')"
            :label="$t('Hochzeitsurkunde')"
            :submitted-label="$t('Vorhanden')"
            :family-allowance-id="value.id"
            @add="addFile"
            @change="updateFile"
          ></file-selector>

          <file-selector
            type="divorce_document"
            :file="fileByType('divorce_document')"
            :label="$t('Scheidungsurteil')"
            :submitted-label="$t('Vorhanden')"
            :family-allowance-id="value.id"
            @add="addFile"
            @change="updateFile"
          ></file-selector>

          <v-switch
            v-model="value.it_registration_family_allowances_send"
            :label="$t('Anmeldung für Familienzulagen abgesendet')"
            @change="update('it_registration_family_allowances_send')"
          ></v-switch>

          <v-switch
            v-model="value.claim_id_received"
            :label="$t('Anspruchsausweis erhalten')"
            @change="update('claim_id_received')"
          ></v-switch>

          <DatePicker
            v-model="value.claim_id_expiration_date"
            :label="$t('Anspruchsausweis Verfallsdatum')"
            @input="update('claim_id_expiration_date')"
          ></DatePicker>
        </template>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import FileSelector from './FileSelector'

export default {
  components: {
    FileSelector,
    DatePicker
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
  data() {
    return {
      civilStates: [
        {
          value: 'single',
          text: this.$t('Ledig')
        },
        {
          value: 'married',
          text: this.$t('Verheirated')
        },
        {
          value: 'divorced',
          text: this.$t('Geschieden')
        },
        {
          value: 'widowed',
          text: this.$t('Verwitwet')
        }
      ]
    }
  },
  computed: {
    update() {
      return this._.debounce(key => {
        this.axios.$patch(`family-allowances/${this.value.id}`, {
          [key]: this.value[key]
        }).catch(() => {
          this.$store.dispatch('error', this.$t('unbekannter-fehler'))
        })
      }, 300)
    }
  },
  methods: {
    fileByType(type) {
      return this.value.files.find(f => f.type === type)
    },
    updateFile(file) {
      const originalFile = this.value.files.find(f => f.type === file.type)
      const index = this.value.files.indexOf(originalFile)

      this.value.files[index] = file

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
