<template>
  <div class="mt-10">
    <p class="font-weight-bold">
      {{ label }}
    </p>

    <v-btn
      v-if="value.length > displayableQuarters.length"
      small
      text
      @click="showAll = true"
    >
      {{ $t('Alle anzeigen') }}
    </v-btn>
    <v-btn
      v-else
      small
      text
      @click="showAll = false"
    >
      {{ $t('Weniger anzeigen') }}
    </v-btn>

    <v-row
      v-for="quarter of displayableQuarters"
      :key="quarter.id"
      no-gutters
    >
      <v-col
        cols="12"
        md="8"
      >
        <QuarterPicker
          v-model="quarter.expiration_date"
          :label="$t('Quartal')"
          @input="editQuarter(quarter)"
        ></QuarterPicker>
      </v-col>

      <v-col
        cols="12"
        md="3"
      >
        <v-switch
          v-model="quarter.value"
          :label="confirmationLabel"
          @change="editQuarter(quarter)"
        ></v-switch>
      </v-col>

      <v-col
        cols="12"
        md="1"
      >
        <v-btn
          class="mt-3"
          icon
          color="red"
          @click="deleteQuarter(quarter)"
        >
          <v-icon>delete</v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <v-btn
      text
      color="primary"
      @click="addQuarter"
    >
      {{ $t('Quartal hinzufügen') }}
    </v-btn>
  </div>
</template>

<script>
import QuarterPicker from '@/components/general/QuarterPicker'

export default {
  components: {
    QuarterPicker
  },
  props: {
    value: {
      type: Array,
      default: () => []
    },
    label: {
      type: String,
      default: null
    },
    type: {
      type: Number,
      required: true
    },
    confirmationLabel: {
      type: String,
      default: 'eingereicht'
    },
    parentId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      showAll: false
    }
  },
  computed: {
    displayableQuarters() {
      if (this.showAll) return this.value

      const quarters = [...this.value]
      quarters.splice(0, quarters.length - 2)
      return quarters
    }
  },
  methods: {
    addQuarter() {
      this.axios.$post('quarters', {
        family_allowance_id: this.parentId,
        type: this.type
      }).then(data => {
        this.value.push(data)
        this.$emit('input', [...this.value])
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
      })
    },
    deleteQuarter(quarter) {
      this.axios.delete(`quarters/${quarter.id}`).then(() => {
        const index = this.value.indexOf(quarter)
        this.value.splice(index, 1)
        this.$emit('input', [...this.value])
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
      })
    },
    editQuarter(quarter) {
      this.axios.patch(`quarters/${quarter.id}`, quarter).catch(() => {
        this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
      })
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
