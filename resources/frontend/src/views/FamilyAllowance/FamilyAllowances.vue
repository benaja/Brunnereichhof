<template>
  <fragment>
    <navigation-bar
      :title="$t('Familienzulagen')"
      full-width
    ></navigation-bar>
    <v-container fluid>
      <v-data-table
        :items="familyAllowances"
        :headers="headers"
      >
        <template v-slot:item="{item}">
          <tr>
            <td>
              {{ item.family_allowanceable.lastname }}
            </td>
            <td>
              {{ item.family_allowanceable.firstname }}
            </td>
            <td>
              <span v-if="item.civil_status === 'single'">
                {{ $t('Nicht benötigt') }}
              </span>
              <span v-else-if="item.marriageDocument && item.marriageDocument.is_submitted">
                {{ $t('Vorhanden') }}
              </span>
              <span
                v-else
                class="red--text"
              >
                {{ $t('Nicht vorhanden') }}
              </span>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-container>
  </fragment>
</template>

<script>
export default {
  data() {
    return {
      familyAllowances: [],
      headers: [
        {
          text: this.$t('Nachname'),
          value: 'family_allowanceable.lastname'
        },
        {
          text: this.$t('Vorname'),
          value: 'family_allowanceable.firstname'
        },
        {
          text: this.$t('Hochzeitsurkunde'),
          value: 'marriageDocument'
        }
      ]
    }
  },
  mounted() {
    this.axios.$get('family-allowances').then(({ data }) => {
      this.familyAllowances = data.map(familyAllowance => {
        const marriageDocument = this.fileByType(familyAllowance, 'marriage_document')
        // if (familyAllowance.civil_status === 'single') {
        //   marriageDocument = this.$t('Nicht benötigt')
        // } else if (marriageDocument && marriageDocument.is_submitted) {
        //   marriageDocument = this.$t('Vorhanden')
        // } else {
        //   marriageDocument = this.$t('Nicht vorhanden')
        // }

        return {
          ...familyAllowance,
          marriageDocument
        }
      })
    }).catch(() => {
      this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
    })
  },
  methods: {
    fileByType(familyAllowance, type) {
      return familyAllowance.files.find(f => f.type === type)
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
