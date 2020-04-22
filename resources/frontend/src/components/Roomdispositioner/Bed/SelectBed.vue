<template>
  <div>
    <h3>Betten</h3>
    <template v-if="value">
      <div
        v-for="(bed, index) of value.filter(b => !b.pivot || !b.pivot.deleted_at)"
        :key="index + '' + bed.id"
      >
        <p class="my-4">
          {{bed.name}}
          <v-btn
            v-if="!disabled"
            text
            icon
            color="red"
            class="float-right delete-bed-icon"
            @click="removeBed(bed)"
          >
            <v-icon>delete</v-icon>
          </v-btn>
        </p>
      </div>
    </template>
    <v-row v-if="!disabled">
      <v-col cols="12">
        <v-autocomplete
          v-model="selectedBed"
          autocomplete="off"
          label="Bett auswählen"
          :items="beds"
          item-value="id"
          item-text="name"
          @input="change"
          color="blue"
          item-color="blue"
        ></v-autocomplete>
      </v-col>
      <v-col cols="12">
        <v-menu :close-on-content-click="false" :nudge-width="300" offset-x v-model="addBedModel">
          <template v-slot:activator="{ on }">
            <v-btn color="blue" class="white--text" v-on="on">Neues Bett erstellen</v-btn>
          </template>
          <add-bed v-model="addBedModel" @add="addBed"></add-bed>
        </v-menu>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import AddBed from '@/components/Roomdispositioner/Bed/AddBed'

export default {
  name: 'SelectBed',
  components: {
    AddBed
  },
  props: {
    value: Array,
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      beds: [],
      addBedModel: false,
      selectedBed: null
    }
  },
  mounted() {
    this.axios.get('/beds').then(respoonse => {
      this.beds = respoonse.data
    })
  },
  methods: {
    addBed(bed) {
      this.beds.push(bed)
      this.value.push(bed)
      if (this.$route.params.id) {
        this.axios.patch(`/rooms/${this.$route.params.id}/beds/${bed.id}`)
      }
    },
    change() {
      let bed = { ...this.beds.find(b => b.id === this.selectedBed) }
      this.value.push(bed)
      if (this.$route.params.id) {
        this.axios.patch(`/rooms/${this.$route.params.id}/beds/${this.selectedBed}`).then(response => {
          bed.pivot = response.data
        })
      }
    },
    removeBed(bed) {
      if (this.$route.params.id) {
        this.axios
          .delete(`/rooms/${this.$route.params.id}/beds/${bed.pivot.id}`)
          .then(() => {
            this.value.splice(this.value.indexOf(bed), 1)
          })
          .catch(error => {
            if (error.includes('Bed is currently in use')) {
              this.$swal('Bed ist zurzeit Besetzt', 'Du kannst nur ein Bett löschen wenn es momentan oder in Zukunft nicht Besetzt ist.', 'error')
            } else {
              this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
            }
          })
      } else {
        this.value.splice(this.value.indexOf(bed), 1)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.delete-bed-icon {
  margin-top: -10px;
}
</style>
