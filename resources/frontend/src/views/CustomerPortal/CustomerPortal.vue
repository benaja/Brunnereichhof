<template>
  <v-container>
    <h1 class="display-1 my-4 text-center text-md-left">{{settings.welcomeText}}</h1>
    <h2 class="mb-4 headline text-center text-md-left">{{settings.subtitle}}</h2>
    <div :class="{card: $vuetify.breakpoint.mdAndUp}">
      <v-row>
        <front-page-card
          icon="/icons/write.svg"
          :title="settings.hourrecordTitle"
          :text="$store.getters.isEditTime ? settings.hourrecordValid : settings.hourrecordInvalid"
          :button-text="$store.getters.isEditTime ? 'Arbeiten Erfassen' : 'Arbeiten Anschauen'"
          :button-link="$store.getters.isEditTime ? '/kundenportal/erfassen' : '/kundenportal/erfassen/details'"
        ></front-page-card>
        <front-page-card
          icon="/icons/see.svg"
          :title="settings.weekRapportTitle"
          :text="settings.weekRapportText"
          button-text="Zu den Wochenrapporten"
          button-link="/kundenportal/wochenrapport"
        ></front-page-card>
        <front-page-card
          icon="/icons/survey.svg"
          :title="settings.surveyTitle"
          :text="settings.surveyText"
          button-text="Zur Umfrage"
          @clickLink="openSurvey"
        ></front-page-card>
      </v-row>
    </div>
  </v-container>
</template>

<script>
import FrontPageCard from '@/components/CustomerPortal/FrontPageCard'

export default {
  components: {
    FrontPageCard
  },
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
p {
  word-break: normal;
}

.card {
  box-shadow: 0 25px 60px rgb(194, 194, 194);
  border-radius: 20px;
  padding: 20px;
  margin-top: 40px;
}
</style>
