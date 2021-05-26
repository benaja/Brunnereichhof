<template>
  <fragment>
    <navigation-bar
      :title="$t('Familienzulagen')"
      full-width
    ></navigation-bar>
    <v-container fluid>
      <div class="d-flex justify-space-between flex-wrap my-4">
        <v-text-field
          v-model="searchString"
          outlined
          :label="$t('Suche')"
          class="search-field"
        ></v-text-field>
        <div class="quarter-picker">
          <QuarterPicker
            v-model="date"
            outlined
            :label="$t('Quartal')"
          ></QuarterPicker>
        </div>
      </div>
      <v-data-table
        :items="familyAllowancesMaped"
        :headers="headers"
        :custom-sort="customSort"
        :items-per-page="-1"
        hide-default-footer
      >
        <template v-slot:item="{item}">
          <tr
            class="family-allowance-table-item"
            @click="selectedFamilyAllowance = item"
          >
            <td>
              {{ item.family_allowanceable.lastname }} {{ item.family_allowanceable.firstname }}
            </td>

            <!-- Marriage Document -->
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

            <!-- Birth Documents of children -->
            <td
              :class="{'red--text': item.childrenWithBirthDocument.length < item.children.length}"
            >
              {{ item.childrenWithBirthDocument.length }}/{{ item.children.length }}
            </td>

            <!-- School Confirmations of children -->
            <td
              :class="{'red--text': item.childrenWithSchoolConfirmatin.length
                < item.childrenAbove16.length}"
            >
              {{ item.childrenWithSchoolConfirmatin.length }}/{{ item.childrenAbove16.length }}
            </td>

            <!-- E411 handed out-->
            <td>
              <span v-if="!item.needs_e411_form">
                {{ $t('Nicht benötigt') }}
              </span>
              <span v-else-if="item.is_e411_handed_out">
                {{ $t('Ja') }}
              </span>
              <span
                v-else
                class="red--text"
              >
                {{ $t('Nein') }}
              </span>
            </td>

            <!-- E411 submitted -->
            <td>
              <span v-if="!item.needs_e411_form">
                {{ $t('Nicht benötigt') }}
              </span>
              <span v-else-if="item.e411 && item.e411.is_submitted">
                {{ $t('Ja') }}
              </span>
              <span
                v-else
                class="red--text"
              >
                {{ $t('Nein') }}
              </span>
            </td>

            <td>
              {{ item.it_registration_family_allowances_send ? $t('Ja') : $t('Nein') }}
            </td>
            <td>
              <span
                v-if="!item.claimIDValid"
                class="red--text"
              >
                {{ $t('Abgelaufen') }}
              </span>
              <span v-else>
                {{ item.claim_id_received ? $t('Ja') : $t('Nein') }}
              </span>
            </td>

            <!-- Employer confirmation for quarter -->
            <td>
              <span v-if="item.employerConfirmation && item.employerConfirmation.value">
                {{ $t('Ja') }}
              </span>
              <span
                v-else
                class="red--text"
              >
                {{ $t('Nein') }}
              </span>
            </td>

            <!-- Credit to Eichhof for quarter -->
            <td>
              <span v-if="item.creditToEchhof && item.creditToEchhof.value">
                {{ $t('Ja') }}
              </span>
              <span
                v-else
                class="red--text"
              >
                {{ $t('Nein') }}
              </span>
            </td>

            <!-- FamilyAllowances paid for quarter -->
            <td>
              <span v-if="item.familyAllowancesPaid && item.familyAllowancesPaid.value">
                {{ $t('Ja') }}
              </span>
              <span
                v-else
                class="red--text"
              >
                {{ $t('Nein') }}
              </span>
            </td>
          </tr>
        </template>
      </v-data-table>

      <v-dialog
        :value="!!selectedFamilyAllowance"
        width="900"
        @input="selectedFamilyAllowance = null"
      >
        <EditFamilyAllowance
          v-model="selectedFamilyAllowance"
          @close="selectedFamilyAllowance = null"
        ></EditFamilyAllowance>
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import QuarterPicker from '@/components/general/QuarterPicker'
import EditFamilyAllowance from '@/components/FamilyAllowance/EditFamilyAllowance'

export default {
  components: {
    QuarterPicker,
    EditFamilyAllowance
  },
  data() {
    return {
      date: this.$moment().format('YYYY-MM-DD'),
      familyAllowances: [],
      selectedFamilyAllowance: null,
      searchString: null,
      headers: [
        {
          text: this.$t('Name'),
          value: 'family_allowanceable.lastname'
        },
        {
          text: this.$t('Hochzeitsurkunde'),
          value: 'marriageDocument'
        },
        {
          text: this.$t('Geburtsurkunden'),
          value: 'childrenWithBirthDocument'
        },
        {
          text: this.$t('Schulbestätigugen'),
          value: 'children'
        },
        {
          text: this.$t('E411 ausgeteilt')
        },
        {
          text: this.$t('E411 eingegangen')
        },
        {
          text: this.$t('Anmeldung abgesendet'),
          value: 'it_registration_family_allowances_send'
        },
        {
          text: this.$t('Anspruchsausweis erhalten'),
          value: 'claim_id_received'
        },
        {
          text: this.$t('Arbeitgeberbescheinigung')
        },
        {
          text: this.$t('Gutschrift FZ an Eichhof')
        },
        {
          text: this.$t('Familienzulagen ausbezahlt')
        }
      ]
    }
  },
  computed: {
    familyAllowancesMaped() {
      return this.familyAllowances.filter(e => !this.searchString
        || `${e.family_allowanceable.lastname} ${e.family_allowanceable.lastname}`.toLowerCase().includes(this.searchString.toLowerCase()))
        .map(familyAllowance => {
          const childrenAbove16 = familyAllowance.children
            .filter(c => this.$moment().diff(this.$moment(c.birthdate), 'years') >= 16)
          const selectedQuarter = this.$moment(this.date).quarter()

          return {
            ...familyAllowance,
            marriageDocument: this.fileByType(familyAllowance, 'marriage_document'),
            e411: this.fileByType(familyAllowance, 'e411'),
            childrenWithBirthDocument: this.childrenWithDocument(familyAllowance.children, 'birth_document'),
            childrenAbove16,
            childrenWithSchoolConfirmatin: this.childrenWithDocument(childrenAbove16, 'school_confirmation'),
            claimIDValid: !familyAllowance.claim_id_received
            || !familyAllowance.claim_id_expiration_date
            || this.$moment(familyAllowance.claim_id_expiration_date).isSameOrAfter(this.$moment(), 'day'),
            employerConfirmation: familyAllowance.employer_confirmation
              .find(e => this.$moment(e.expiration_date).quarter() === selectedQuarter),
            creditToEchhof: familyAllowance.credit_to_eichhof
              .find(c => this.$moment(c.expiration_date).quarter() === selectedQuarter),
            familyAllowancesPaid: familyAllowance.family_allowances_paid
              .find(f => this.$moment(f.expiration_date).quarter() === selectedQuarter)
          }
        })
    }
  },
  mounted() {
    this.axios.$get('family-allowances').then(({ data }) => {
      this.familyAllowances = data
    }).catch(() => {
      this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
    })
  },
  methods: {
    fileByType(familyAllowance, type) {
      return familyAllowance.files.find(f => f.type === type)
    },
    childrenWithDocument(children, document) {
      return children.filter(c => {
        const birthDocument = this.fileByType(c, document)

        return birthDocument && birthDocument.is_submitted
      })
    },
    customSort(items, sortBy, sortDesc) {
      if (sortBy.includes('marriageDocument')) {
        items.sort(this.marriageDocumentSort)
      }
      if (sortBy.includes('childrenWithBirthDocument')) {
        items.sort(this.childrenBirthDocumentSort)
      }


      if (sortDesc[0]) {
        return items
      }
      return items.reverse()
    },
    documentSubmitted(document) {
      return document && document.is_submitted
    },

    marriageDocumentSort(a, b) {
      let stateA = this.documentSubmitted(a.marriageDocument) ? 0 : 2
      let stateB = this.documentSubmitted(b.marriageDocument) ? 0 : 2

      if (a.civil_status === 'single') {
        stateA = 1
      }
      if (b.civil_status === 'single') {
        stateB = 1
      }

      return stateA - stateB
    },
    childrenBirthDocumentSort(a, b) {
      let scoreA; let
        scoreB = 0

      if (a.childrenWithBirthDocument.lenght < a.children.length) scoreA = 1
      if (b.childrenWithBirthDocument.length < b.children.lenght) scoreB = 1

      return scoreA - scoreB
    }
  }
}
</script>

<style lang="scss" scoped>
.quarter-picker {
  max-width: 150px;
}

.search-field {
  max-width: 400px;
}


</style>

<style lang="scss">
.family-allowance-table-item {
  cursor: pointer;
}
</style>
