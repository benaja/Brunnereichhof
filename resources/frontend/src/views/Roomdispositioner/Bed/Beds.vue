<template>
  <fragment>
    <navigation-bar
      title="Betten"
      :loading="isLoading.beds"
      color="blue"
    ></navigation-bar>
    <v-container>
      <v-list class="pa-0 elevation-2">
        <v-list-item
          v-for="bed of beds"
          :key="bed.id"
          :to="'/beds/' + bed.id"
        >
          <v-list-item-content>
            <p class="mt-3">
              <strong>{{ bed.name }}</strong>
              {{ bed.width }}
            </p>
          </v-list-item-content>
          <v-list-item-action>{{ bed.places }} Plätze</v-list-item-action>
        </v-list-item>
      </v-list>
      <v-dialog
        v-model="addModel"
        width="900"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
            fixed
            bottom
            right
            fab
            color="blue"
            v-on="on"
          >
            <v-icon color="white">
              add
            </v-icon>
          </v-btn>
        </template>
        <add-bed
          v-model="addModel"
          @add="add"
        />
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import AddBed from '@/components/Roomdispositioner/Bed/AddBed'
import { mapGetters } from 'vuex'

export default {
  name: 'Beds',
  components: {
    AddBed
  },
  data() {
    return {
      addModel: false
    }
  },
  computed: {
    ...mapGetters(['beds', 'isLoading'])
  },
  mounted() {
    this.$store.dispatch('fetchBeds')
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
