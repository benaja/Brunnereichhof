<template>
  <v-row>
    <v-col
      cols="12"
      md="4"
    >
      <v-text-field
        v-model="child.firstname"
        :label="$t('Vorname')"
        hide-details
        @input="update"
      ></v-text-field>
    </v-col>
    <v-col
      cols="12"
      md="4"
    >
      <v-text-field
        v-model="child.lastname"
        :label="$t('Nachname')"
        hide-details
        @input="update"
      ></v-text-field>
    </v-col>
    <v-col
      cols="12"
      md="3"
    >
      <date-picker
        v-model="child.birthdate"
        :label="$t('Geburtstag')"
        @input="update"
      ></date-picker>
    </v-col>
    <v-col
      cols="12"
      md="1"
    >
      <v-btn
        color="red"
        icon
        class="mt-3"
        @click="deleteChild"
      >
        <v-icon>delete</v-icon>
      </v-btn>
    </v-col>
  </v-row>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import { confirmAction } from '@/utils'

export default {
  components: {
    DatePicker
  },
  props: {
    child: {
      type: Object,
      required: true
    }
  },
  computed: {
    update() {
      return this._.debounce(() => {
        this.axios.$patch(`children/${this.child.id}`, this.child).catch(() => {
          this.$store.dispatch('error', 'Es ist ein unerwarteter Fehler aufgetreten')
        })
      }, 300)
    }
  },
  methods: {
    deleteChild() {
      confirmAction().then(value => {
        if (value) {
          this.axios.$delete(`children/${this.child.id}`)
            .then(() => {
              this.$emit('remove', this.child)
            }).catch(() => {
              this.$store.dispatch('error', 'Es ist ein unerwarteter Fehler aufgetreten')
            })
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
