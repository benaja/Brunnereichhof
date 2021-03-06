<template>
  <fragment>
    <navigation-bar
      :title="$t('Autos')"
    ></navigation-bar>
    <v-container class="mb-11">
      <v-text-field
        v-model="searchString"
        label="Suchen"
        prepend-icon="search"
        @input="debounceSearch"
      ></v-text-field>
      <v-data-table
        :items="cars"
        :headers="headers"
        :items-per-page="-1"
        :loading="isLoading.cars"
        hide-default-footer
      >
        <template v-slot:item="{item}">
          <tr>
            <td>
              <div
                v-if="item.image"
                :src="item.small_image_url"
                class="car-thumbnail"
                :style="{backgroundImage: `url(${item.small_image_url})`}"
              >
              </div>
            </td>
            <td>{{ item.name }}</td>
            <td>{{ item.seats }}</td>
            <td>{{ item.number }}</td>
            <td>{{ item.fuel === 'gas' ? $t('Benzin') : $t('Diesel') }}</td>
            <td v-if="isAllowedToEdit">
              <div class="d-flex jsutify-end">
                <v-btn
                  icon
                  @click="editCar = item"
                >
                  <v-icon>edit</v-icon>
                </v-btn>
                <v-btn
                  icon
                  @click="deleteCar(item)"
                >
                  <v-icon>delete</v-icon>
                </v-btn>
              </div>
            </td>
          </tr>
        </template>
      </v-data-table>

      <v-dialog
        v-if="isAllowedToEdit"
        v-model="addCar"
        width="900"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            fixed
            bottom
            right
            fab
            color="primary"
            v-on="on"
          >
            <v-icon color="white">
              add
            </v-icon>
          </v-btn>
        </template>
        <add-car
          v-model="addCar"
          @add="searchString = null"
        />
      </v-dialog>

      <v-dialog
        :value="!!editCar"
        width="900"
        @input="editCar = null"
      >
        <edit-car
          :value="editCar"
          @close="editCar = null"
        ></edit-car>
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import AddCar from '@/components/ResourcePlanner/cars/AddCar'
import EditCar from '@/components/ResourcePlanner/cars/EditCar'
import { mapGetters } from 'vuex'
import { confirmAction } from '@/utils'

export default {
  components: {
    AddCar,
    EditCar
  },
  data () {
    return {
      addCar: false,
      editCar: null,
      searchString: null
    }
  },
  computed: {
    ...mapGetters(['cars', 'isLoading']),
    debounceSearch() {
      return this._.debounce(() => {
        this.$store.dispatch('fetchCars', {
          search: this.searchString
        })
      }, 300)
    },
    isAllowedToEdit() {
      return this.$auth.user().hasPermission(['superadmin'], ['cars_write'])
    },
    headers() {
      const headers = [
        {
          text: this.$i18n.t('Bild'),
          width: 70
        },
        {
          text: this.$i18n.t('Name'),
          value: 'name'
        },
        {
          text: this.$t('Sitzplätze'),
          value: 'seats'
        },
        {
          text: this.$t('BE-Nummer'),
          value: 'number'
        },
        {
          text: this.$t('Treibstoff'),
          value: 'fuel'
        }
      ]

      if (this.isAllowedToEdit) {
        headers.push({
          text: this.$t('Aktionen'),
          width: 100
        })
      }

      return headers
    }
  },
  mounted() {
    this.$store.dispatch('fetchCars')
  },
  methods: {
    deleteCar(car) {
      confirmAction(this.$t('auto-wirklich-löschen', { name: car.name })).then(value => {
        if (value) {
          this.$store.dispatch('deleteCar', car.id).catch(() => {
            this.$swal(this.$t('unbekannter-fehler'), this.$t('fehler-beim-löschen'), 'error')
          })
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.car-thumbnail {
  height: 40px;
  width: 40px;
  background-size: cover;
  background-position: center;
  border-radius: 50%;
}
</style>
