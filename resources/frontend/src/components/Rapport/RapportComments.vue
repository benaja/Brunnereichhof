<template>
  <tr>
    <th>Kommentar</th>
    <td
      v-for="day of days"
      :key="day"
    >
      <text-area
        v-model="rapport[day]"
        class="mt-1"
        placeholder="Bemerkung"
        :readonly="!hasPermisstionToChangeRapport"
        :rows="1"
        hide-details
        @input="updateComment(day)"
      ></text-area>
    </td>
  </tr>
</template>

<script>
import { debounce } from 'lodash'
import { TextArea } from '@/components/FormComponents'

export default {
  components: {
    TextArea
  },
  props: {
    rapport: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      days: [
        'comment_mo',
        'comment_tu',
        'comment_we',
        'comment_th',
        'comment_fr',
        'comment_sa'
      ]
    }
  },
  computed: {
    hasPermisstionToChangeRapport() {
      return this.$auth.user().hasPermission(['superadmin'], ['rapport_write'])
    }
  },
  methods: {
    updateComment: debounce(function () {
      this.$store.commit('isSaving', true)
      this.axios
        .patch(`rapports/${this.rapport.id}`, {
          ...this.days.reduce((prev, curr) => {
            prev[curr] = this.rapport[curr]
            return prev
          }, {})
        })
        .catch(() => {
          this.$swal('Fehler beim speicher', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        }).finally(() => {
          this.$store.commit('isSaving', false)
        })
    }, 400)
  }
}
</script>

<style lang="scss" scoped>

th {
  vertical-align: middle;
  text-align: left;
}


</style>
