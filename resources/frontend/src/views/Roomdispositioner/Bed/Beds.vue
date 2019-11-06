<template>
  <v-container>
    <h1>Betten</h1>
    <v-list class="pa-0 elevation-2">
      <v-list-item v-for="bed of beds" :key="bed.id" :to="'/beds/' + bed.id">
        <v-list-item-content>
          <p class="mt-3">
            <strong>{{bed.name}}</strong>
            {{bed.width}}
          </p>
        </v-list-item-content>
        <v-list-item-action>{{bed.places}} Plätze</v-list-item-action>
      </v-list-item>
    </v-list>
    <v-menu
      :close-on-content-click="false"
      v-model="addModel"
      right
      content-class="bed-menu"
      max-width="500"
      min-width="500"
    >
      <template v-slot:activator="{ on }">
        <v-btn
          fixed
          bottom
          right
          fab
          color="blue"
          v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
          v-on="on"
        >
          <v-icon color="white">add</v-icon>
        </v-btn>
      </template>
      <add-bed v-model="addModel" @add="add"></add-bed>
    </v-menu>
  </v-container>
</template>

<script>
import AddBed from '@/components/Roomdispositioner/Bed/AddBed'

export default {
  name: 'Beds',
  components: {
    AddBed
  },
  data() {
    return {
      beds: [],
      addModel: false
    }
  },
  mounted() {
    this.axios.get('/beds').then(response => {
      this.beds = response.data
    })
  },
  methods: {
    add(bed) {
      this.beds.push(bed)
    }
  }
}
</script>

<style lang="scss" scoped>
.bed-menu {
  bottom: 10px;
  top: unset !important;
}
</style>
