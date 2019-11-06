<template>
  <v-row>
    <v-col cols="2" xl="1" class="hidden-sm-and-down pl-2 pt-3">
      <p class="font-weight-bold">Wochentag</p>
    </v-col>
    <v-col 1 cols="10" xl="1" class="hidden-sm-and-down">
      <v-row class="pt-3">
        <v-col cols="2" class="font-weight-bold">Montag</v-col>
        <v-col cols="2" class="font-weight-bold">Dienstag</v-col>
        <v-col cols="2" class="font-weight-bold">Mittwoch</v-col>
        <v-col cols="2" class="font-weight-bold">Donnerstag</v-col>
        <v-col cols="2" class="font-weight-bold">Freitag</v-col>
        <v-col cols="2" class="font-weight-bold">Samstag</v-col>
      </v-row>
    </v-col>
    <v-col cols="12">
      <v-divider></v-divider>
    </v-col>
    <v-col cols="2" xl="1" class="hidden-sm-and-down pl-2 pt-3">
      <p class="font-weight-bold mt-3">Kommentar</p>
    </v-col>
    <v-col 1 cols="10" xl="1" class="hidden-sm-and-down">
      <v-row class="pt-3">
        <v-col cols="2">
          <v-textarea
            v-model="rapport.comment_mo"
            solo
            text
            placeholder="Bemerkung"
            auto-grow
            rows="1"
            @change="change('comment_mo')"
          ></v-textarea>
        </v-col>
        <v-col cols="2">
          <v-textarea
            v-model="rapport.comment_tu"
            solo
            text
            placeholder="Bemerkung"
            auto-grow
            rows="1"
            @change="change('comment_tu')"
          ></v-textarea>
        </v-col>
        <v-col cols="2">
          <v-textarea
            v-model="rapport.comment_we"
            solo
            text
            placeholder="Bemerkung"
            auto-grow
            rows="1"
            @change="change('comment_we')"
          ></v-textarea>
        </v-col>
        <v-col cols="2">
          <v-textarea
            v-model="rapport.comment_th"
            solo
            text
            placeholder="Bemerkung"
            auto-grow
            rows="1"
            @change="change('comment_th')"
          ></v-textarea>
        </v-col>
        <v-col cols="2">
          <v-textarea
            v-model="rapport.comment_fr"
            solo
            text
            placeholder="Bemerkung"
            auto-grow
            rows="1"
            @change="change('comment_fr')"
          ></v-textarea>
        </v-col>
        <v-col cols="2">
          <v-textarea
            v-model="rapport.comment_sa"
            solo
            text
            placeholder="Bemerkung"
            auto-grow
            rows="1"
            @change="change('comment_sa')"
          ></v-textarea>
        </v-col>
      </v-row>
    </v-col>
  </v-row>
</template>

<script>
export default {
  name: 'TableHeader',
  components: {},
  props: ['rapport'],
  mounted() {},
  methods: {
    change(changedElement) {
      this.axios
        .patch(`${process.env.VUE_APP_API_URL}rapport/${this.rapport.id}`, {
          [changedElement]: this.rapport[changedElement]
        })
        .catch(() => {
          this.$swal('Fehler beim speicher', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

<style lang="scss">
.rapport-table-header {
  .v-input__slot {
    padding-left: 0;
  }
}
</style>
