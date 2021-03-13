<template>
  <fragment>
    <navigation-bar
      :title="$t('Werkzeuge')"
    ></navigation-bar>
    <v-container>
      <v-text-field
        v-model="searchString"
        label="Suchen"
        prepend-icon="search"
        @input="debounceSearch"
      ></v-text-field>
      <v-data-table
        :items="tools"
        :headers="headers"
        :items-per-page="-1"
        :loading="isLoading.tools"
        hide-default-footer
      >
        <template v-slot:item="{item}">
          <tr>
            <td>{{ item.name }}</td>
            <td>{{ item.amount }}</td>
            <td>
              <div class="d-flex jsutify-end">
                <v-btn
                  icon
                  @click="editTool = item"
                >
                  <v-icon>edit</v-icon>
                </v-btn>
                <v-btn
                  icon
                  @click="deleteTool(item)"
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
        v-model="addTool"
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
        <add-tool
          v-model="addTool"
          @add="searchString = null"
        />
      </v-dialog>

      <!-- Edit Tool -->
      <v-dialog
        :value="!!editTool"
        width="900"
        @input="editTool = null"
      >
        <edit-tool
          :value="editTool"
          @close="editTool = null"
        ></edit-tool>
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import AddTool from '@/components/ResourcePlanner/tools/AddTool'
import EditTool from '@/components/ResourcePlanner/tools/EditTool'
import { mapGetters } from 'vuex'
import { confirmAction } from '@/utils'

export default {
  components: {
    AddTool,
    EditTool
  },
  data () {
    return {
      addTool: false,
      editTool: null,
      searchString: null,
      headers: [
        {
          text: this.$i18n.t('Name'),
          value: 'name'
        },
        {
          text: this.$t('Menge'),
          value: 'amount'
        },
        {
          text: this.$t('Aktionen'),
          width: 100
        }
      ]
    }
  },
  computed: {
    ...mapGetters(['tools', 'isLoading']),
    debounceSearch() {
      return this._.debounce(() => {
        this.$store.dispatch('fetchTools', {
          search: this.searchString
        })
      }, 300)
    }
  },
  mounted() {
    this.$store.dispatch('fetchTools')
  },
  methods: {
    deleteTool(tool) {
      confirmAction(this.$t('werkzeug-wirklich-löschen', { name: tool.name })).then(value => {
        if (value) {
          this.$store.dispatch('deleteTool', tool.id).catch(() => {
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
