<template>
  <v-form
    ref="form"
    lazy-validation
    class="px-3"
  >
    <date-picker
      v-model="value.entry"
      label="Von"
      :rules="[rules.required]"
      color="blue"
      @input="getBeds"
    ></date-picker>
    <date-picker
      v-model="value.exit"
      label="Bis"
      :rules="[rules.after]"
      color="blue"
      @input="getBeds"
    ></date-picker>
    <v-select
      v-model="value.bed_room_pivot.room_id"
      :items="availableRooms"
      label="Raum"
      item-value="id"
      item-text="name"
      :rules="[rules.required]"
      color="blue"
      item-color="blue"
      @input="getBeds"
    ></v-select>
    <v-select
      v-model="value.bed_room_pivot.id"
      :items="beds"
      :loading="loadingBeds"
      label="Bett"
      item-value="pivot.id"
      item-text="name"
      :rules="[rules.required]"
      no-data-text="Kein freies Bett vorhanden."
      color="blue"
      item-color="blue"
    ></v-select>
    <select-employee
      v-model="value.employee_id"
      :rules="[rules.required]"
    ></select-employee>
  </v-form>
</template>

<script>
import { rules } from '@/utils'
import DatePicker from '@/components/general/DatePicker'
import SelectEmployee from '@/components/Roomdispositioner/SelectEmployee'
import { mapGetters } from 'vuex'

export default {
  components: {
    DatePicker,
    SelectEmployee
  },
  props: {
    value: {
      type: Object,
      required: true
    },
    originalRoomId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      rules: {
        ...rules,
        after: () => this.$moment(this.value.entry).isSameOrBefore(this.value.exit, 'day') || 'Das Datum muss nach dem Startdatum sein.'
      },
      beds: [],
      loadingBeds: false
    }
  },
  computed: {
    ...mapGetters(['rooms']),
    availableRooms() {
      const rooms = [...this.rooms]
      if (this.originalRoomId && !this.rooms.find(r => r.id === this.originalRoomId)) {
        rooms.push(this.value.bed_room_pivot.room)
      }
      return rooms
    }
  },
  mounted() {
    this.$store.dispatch('fetchRooms').then(() => {
      this.getBeds()
    })
  },
  methods: {
    getBeds() {
      if (this.value.bed_room_pivot.room_id) {
        this.loadingBeds = true
        this.axios.get(`/rooms/${this.value.bed_room_pivot.room_id}/beds?entry=${this.value.entry}&exit=${this.value.exit}`).then(response => {
          this.loadingBeds = false
          if (!response.data.find(b => b.pivot.id === this.value.bed_room_pivot.id)
            && this.value.bed_room_pivot.room_id === this.originalRoomId) {
            response.data.push({
              ...this.value.bed_room_pivot.bed,
              pivot: this.value.bed_room_pivot
            })
          }
          for (const bed of response.data) {
            bed.bed_room_pivot = bed.pivot
          }
          this.beds = response.data
        })
      }
    },
    validate() {
      return this.$refs.form.validate()
    },
    reset() {
      return this.$refs.form.reset()
    }
  }
}
</script>
