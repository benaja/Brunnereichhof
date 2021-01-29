<template>
  <fragment>
    <navigation-bar
      :title="$t('Einsatzplaner.Werkzeuge')"
    ></navigation-bar>
    <v-container>
      <v-data-table
        :items="tools"
        :headers="headers"
        :items-per-page="-1"
        :loading="isLoading.tools"
      >
        <template v-slot:item="{item}">
          <tr>
            <td>{{ item.name }}</td>
            <td>{{ item.amount }}</td>
            <td>
              <div class="d-flex jsutify-end">
                <v-btn
                  icon
                  @click="editTransaction = item"
                >
                  <v-icon>edit</v-icon>
                </v-btn>
                <v-btn
                  icon
                  @click="editTransaction = item"
                >
                  <v-icon>delete</v-icon>
                </v-btn>
              </div>
            </td>
          </tr>
        </template>
      </v-data-table>
      <v-dialog
        v-model="addTool"
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
        <add-tool v-model="addTool" />
      </v-dialog>
    </v-container>
  </fragment>
</template>

<script>
import AddTool from '@/components/ResoucePlanner/tools/AddTool'
import { mapGetters } from 'vuex'

export default {
  components: {
    AddTool
  },
  data () {
    return {
      addTool: false,
      headers: [
        {
          text: this.$i18n.t('Einsatzplaner.Name'),
          value: 'name'
        },
        {
          text: this.$t('Einsatzplaner.Menge'),
          value: 'amount'
        },
        {
          text: this.$t('Einsatzplaner.Aktionen'),
          width: 100
        }
      ]
    }
  },
  computed: {
    ...mapGetters(['tools', 'isLoading'])
  },
  mounted() {
    this.$store.dispatch('fetchTools')
  }
}
</script>

<style>

</style>
