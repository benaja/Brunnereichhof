<template>
  <v-container>
    <h1>{{settings.welcomeText}}</h1>
    <h2 class="mb-4 subheading">{{settings.subtitle}}</h2>
    <v-row>
      <v-col>
        <v-card width="500" max-width="100%" class="mb-4">
          <v-img src="/field.jpg" aspect-ratio="2"></v-img>
          <v-card-title primary-title class="card-title">
            <div>
              <h3 class="headline mb-0">{{settings.hourrecordTitle}}</h3>
              <p v-if="$store.getters.isEditTime">{{settings.hourrecordValid}}</p>
              <p v-else>{{settings.hourrecordInvalid}}</p>
            </div>
          </v-card-title>
          <v-card-actions>
            <v-btn
              v-if="$store.getters.isEditTime"
              text
              color="primary"
              to="/kundenportal/erfassen"
            >Arbeiten Erfassen</v-btn>
            <v-btn
              v-else
              text
              color="primary"
              to="/kundenportal/erfassen/details"
            >Arbeiten Anschauen</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col>
        <v-card width="500" max-width="100%" left>
          <v-img src="/cornfield_questionmark.jpg" aspect-ratio="2"></v-img>
          <v-card-title primary-title class="card-title">
            <div>
              <h3 class="headline mb-0">{{settings.surveyTitle}}</h3>
              <p>{{settings.surveyText}}</p>
            </div>
          </v-card-title>
          <v-card-actions>
            <v-btn text color="primary" @click="openSurvey">Zur Umfrage</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'CustomerPortal',
  data() {
    return {
      settings: {}
    }
  },
  mounted() {
    this.axios.get(process.env.VUE_APP_API_URL + 'settings/hourrecords').then(response => {
      response.data.welcomeText = response.data.welcomeText.replace('{name}', this.$auth.user().firstname + ' ' + this.$auth.user().lastname)
      this.settings = response.data
      this.settings.hourrecordValid = this.settings.hourrecordValid.replace('{datum}', this.endDate.toLocaleDateString())
      this.$store.commit('settings', response.data)
    })
  },
  methods: {
    openSurvey() {
      window.open('https://html.brunnereichhof.ch/domains/lohnjaeterei.ch/kundenumfrage/', '_blank')
    }
  },
  computed: {
    endDate() {
      return new Date(this.settings.hourrecordEndDate)
    }
  }
}
</script>

<style lang="scss" scoped>
.card-title {
  min-height: 120px;
  align-items: unset;
  padding-top: 12px;
}
</style>
