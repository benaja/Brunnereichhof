<template>
  <v-expansion-panels
    v-if="value && $auth.user().hasPermission('superadmin', 'family_allowance_read')"
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
          :filable-id="value.id"
          disable-submitted
          :readonly="disabled"
          @add="addFile"
          @change="updateFile"
        ></file-selector>
        <file-selector
          type="foreign_id"
          :file="fileByType('foreign_id')"
          :label="$t('Ausländerausweis')"
          :filable-id="value.id"
          disable-submitted
          :readonly="disabled"
          @add="addFile"
          @change="updateFile"
        ></file-selector>

        <v-select
          v-model="value.civil_status"
          :label="$t('Zivilstand')"
          :items="civilStates"
          :readonly="disabled"
          @input="update('civil_status')"
        ></v-select>

        <v-switch
          v-model="value.has_family_allowance"
          :label="$t('Bansprucht Familienzulagen')"
          :readonly="disabled"
          @change="update('has_family_allowance')"
        ></v-switch>

        <template v-if="value.has_family_allowance">
          <DatePicker
            v-model="value.expiration_of_family_allowance"
            :readonly="disabled"
            :label="$t('Verfallsdatum Familienzulagen')"
            @input="update('expiration_of_family_allowance')"
          ></DatePicker>

          <v-switch
            v-model="value.partner_employed"
            :readonly="disabled"
            :label="$t('Partner erwerbstätig')"
            @change="update('partner_employed')"
          ></v-switch>

          <v-switch
            v-model="value.needs_e411_form"
            :readonly="disabled"
            :label="$t('Benötigt Formular E411')"
            @change="update('needs_e411_form')"
          ></v-switch>

          <v-switch
            v-model="value.is_e411_handed_out"
            :label="$t('E411 ausgeteilt')"
            :readonly="disabled"
            @change="update('is_e411_handed_out')"
          ></v-switch>

          <file-selector
            type="e411"
            :file="fileByType('e411')"
            :label="$t('E411')"
            :filable-id="value.id"
            :readonly="disabled"
            @add="addFile"
            @change="updateFile"
          ></file-selector>

          <file-selector
            type="marriage_document"
            :file="fileByType('marriage_document')"
            :label="$t('Hochzeitsurkunde')"
            :submitted-label="$t('Vorhanden')"
            :filable-id="value.id"
            :readonly="disabled"
            @add="addFile"
            @change="updateFile"
          ></file-selector>

          <file-selector
            type="divorce_document"
            :file="fileByType('divorce_document')"
            :label="$t('Scheidungsurteil')"
            :submitted-label="$t('Vorhanden')"
            :filable-id="value.id"
            :readonly="disabled"
            @add="addFile"
            @change="updateFile"
          ></file-selector>

          <v-switch
            v-model="value.it_registration_family_allowances_send"
            :label="$t('Anmeldung für Familienzulagen abgesendet')"
            :readonly="disabled"
            @change="update('it_registration_family_allowances_send')"
          ></v-switch>

          <v-switch
            v-model="value.claim_id_received"
            :label="$t('Anspruchsausweis erhalten')"
            :readonly="disabled"
            @change="update('claim_id_received')"
          ></v-switch>

          <DatePicker
            v-model="value.claim_id_expiration_date"
            :label="$t('Anspruchsausweis Verfallsdatum')"
            :readonly="disabled"
            @input="update('claim_id_expiration_date')"
          ></DatePicker>

          <Children
            v-model="value.children"
            :family-allowance="value"
            :readonly="disabled"
          ></Children>

          <QuarterConfirmation
            v-model="value.employer_confirmation"
            :label="$t('Arbeitgeberbescheinigung')"
            :parent-id="value.id"
            :type="QuarterType.EmployerConfirmation"
            :readonly="disabled"
          ></QuarterConfirmation>

          <QuarterConfirmation
            v-model="value.credit_to_eichhof"
            :label="$t('Gutschrift FZ an Eichhof')"
            :parent-id="value.id"
            :type="QuarterType.CreditToEichhof"
            :readonly="disabled"
          ></QuarterConfirmation>

          <QuarterConfirmation
            v-model="value.family_allowances_paid"
            :label="$t('Familienzulagen ausbezahlt an Mitarbeiter')"
            :parent-id="value.id"
            :type="QuarterType.FamilyAllowancesPaid"
            :readonly="disabled"
          ></QuarterConfirmation>
        </template>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import { QuarterType } from '@/utils'
import QuarterConfirmation from './QuarterConfirmation'
import FileSelector from './FileSelector'
import Children from './Children'

export default {
  components: {
    FileSelector,
    DatePicker,
    Children,
    QuarterConfirmation
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
      QuarterType,
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
    },
    disabled() {
      return !this.$auth.user().hasPermission('superadmin', 'family_allowance_write')
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
