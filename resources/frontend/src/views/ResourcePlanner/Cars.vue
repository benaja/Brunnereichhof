<template>
  <fragment>
    <navigation-bar
      :title="$t('Einsatzplaner.Werkzeuge')"
    ></navigation-bar>
    <v-container>
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
            <td>{{ item.name }}</td>
            <td>{{ item.seats }}</td>
            <td>{{ item.number }}</td>
            <td>{{ item.fuel }}</td>
            <td>
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

      <!-- Add Tool -->
      <v-dialog
        v-model="addCar"
        width="900"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            v-if="$auth.user().hasPermission(['superadmin'], ['tools_edit'])"
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
        <add-tool
          v-model="addCar"
          @add="searchString = null"
        />
      </v-dialog>

      <!-- Edit Tool -->
      <v-dialog
        :value="!!editCar"
        width="900"
        @input="editCar = null"
      >
        <edit-tool
          :value="editCar"
          @close="editCar = null"
        ></edit-tool>
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import AddTool from '@/components/ResoucePlanner/tools/AddTool'
import EditTool from '@/components/ResoucePlanner/tools/EditTool'
import { mapGetters } from 'vuex'
import { confirmAction } from '@/utils'

export default {
  components: {
    AddTool,
    EditTool
  },
  data () {
    return {
      addCar: false,
      editCar: null,
      searchString: null,
      headers: [
        {
          text: this.$i18n.t('Einsatzplaner.Name'),
          value: 'name'
        },
        {
          text: this.$t('Einsatzplaner.Sitzplätze'),
          value: 'seats'
        },
        {
          text: this.$t('Einsatzplaner.BE-Nummer'),
          value: 'number'
        },
        {
          text: this.$t('Einsatzplaner.Benzin'),
          value: 'fuel'
        },
        {
          text: this.$t('Aktionen'),
          width: 100
        }
      ]
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
    }
  },
  mounted() {
    this.$store.dispatch('fetchCars')
  },
  methods: {
    deleteTool(tool) {
      confirmAction(this.$t('Einsatzplaner.auto-wirklich-löschen', { name: tool.name })).then(value => {
        if (value) {
          this.$store.dispatch('deleteCar', tool.id).catch(() => {
            this.$swal(this.$t('unbekannter-fehler'), this.$t('fehler-beim-löschen'), 'error')
          })
        }
      })
    }
  }
}
</script>

<style>

</style>
