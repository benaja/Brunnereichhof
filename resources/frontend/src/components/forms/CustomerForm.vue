<template>
  <v-form
    ref="form"
    @keyup.native.enter="submit"
  >
    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.firstname"
          label="Vorname*"
          :original="original.firstname"
          :rules="[rules.required]"
          :readonly="readonly"
          @change="$emit('change', 'firstname')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.lastname"
          label="Nachname*"
          :original="original.lastname"
          :rules="[rules.required]"
          :readonly="readonly"
          @change="$emit('change', 'lastname')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
      >
        <v-divider class="mb-8"></v-divider>
        <h3 class="headline">
          Adresse
        </h3>
        <edit-address
          v-model="value.address"
          :readonly="readonly"
          :original="original.address"
          @change="$emit('change', 'address')"
        ></edit-address>
        <v-divider class="mb-8"></v-divider>
        <v-checkbox
          v-model="value.differingBillingAddress"
          :readonly="readonly"
          label="Abweichende Rechnungsadresse"
          @change="$emit('change', 'differingBillingAddress')"
        ></v-checkbox>
      </v-col>
      <v-col
        v-if="value.differingBillingAddress"
        cols="12"
        class="py-0"
      >
        <h3 class="headline">
          Rechnungsadresse
        </h3>
        <edit-address
          v-model="value.billing_address"
          :readonly="readonly"
          :original="original.billing_address"
          @change="$emit('change', 'billing_address')"
        ></edit-address>
        <v-divider class="mb-8"></v-divider>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.mobile"
          label="Mobile"
          :original="original.mobile"
          :readonly="readonly"
          @change="$emit('change', 'mobile')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.phone"
          label="Festnetz"
          :original="original.phone"
          :readonly="readonly"
          @change="$emit('change', 'phone')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.email"
          label="Email"
          type="email"
          :original="original.email"
          :readonly="readonly"
          :rules="[rules.nullableEmail]"
          @change="$emit('change', 'email')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.customer_number"
          label="Kundennummer"
          :original="original.customer_number"
          type="number"
          :readonly="readonly"
          @change="$emit('change', 'customer_number')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.needs_payment_order"
          label="Benötigt Einzahlungsschein"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'needs_payment_order')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.hasCatering"
          label="Verpflegung"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'hasCatering')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.kitchen_infrastructure"
          label="Ausstattung der Küche"
          :original="original.kitchen_infrastructure"
          :readonly="readonly"
          @change="$emit('change', 'kitchen_infrastructure')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.max_catering"
          label="Max Anzahl Verpflegung"
          :original="original.max_catering"
          :readonly="readonly"
          type="number"
          @change="$emit('change', 'max_catering')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.driver_info"
          label="Fahrerinfo"
          :original="original.driver_info"
          :readonly="readonly"
          @change="$emit('change', 'driver_info')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.maps"
          label="Google-Maps Link"
          :original="original.maps"
          :readonly="readonly"
          @change="$emit('change', 'maps')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.comment"
          label="Allgemeine Bemerkung"
          :original="original.comment"
          :readonly="readonly"
          @change="$emit('change', 'comment')"
        ></text-field>
      </v-col>
      <v-col cols="12">
        <p>Projekte</p>
        <edit-projects
          v-model="value.projects"
          :original="original.projects"
          :customer-id="$route.params.id"
          :readonly="readonly"
          @change="$emit('change', 'projects')"
        ></edit-projects>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { TextField } from '@/components/FormComponents'
import EditAddress from '@/components/customer/EditAddress'
import { rules } from '@/utils'
import EditProjects from '@/components/customer/EditProjects'

export default {
  components: {
    TextField,
    EditAddress,
    EditProjects
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    },
    original: {
      type: Object,
      default: () => ({})
    },
    readonly: Boolean
  },
  data() {
    return {
      rules
    }
  },
  methods: {
    validate() {
      return this.$refs.form.validate()
    },
    submit() {
      if (!this.$store.getters.preventFormSubmit) {
        this.$emit('submit')
      }
    }
  }
}
</script>
