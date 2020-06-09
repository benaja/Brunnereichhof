<template>
  <fragment>
    <navigation-bar
      title="Kundenportal"
      :loading="$store.getters.isLoading.settings"
    ></navigation-bar>
    <v-container>
      <h1 class="display-1 my-4 text-center text-md-left">
        {{ settings.welcomeText }}
      </h1>
      <h2 class="mb-4 headline text-center text-md-left">
        {{ settings.subtitle }}
      </h2>
      <div :class="{card: $vuetify.breakpoint.mdAndUp}">
        <v-row>
          <front-page-card
            icon="/icons/write.svg"
            :title="settings.hourrecordTitle"
            :text="$store.getters.isEditTime
              ? settings.hourrecordValid : settings.hourrecordInvalid"
            :button-text="$store.getters.isEditTime ? 'Arbeiten Erfassen' : 'Arbeiten Anschauen'"
            :button-link="$store.getters.isEditTime ? '/kundenportal/erfassen'
              : '/kundenportal/erfassen/details'"
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
  </fragment>
</template>

<script>
import FrontPageCard from '@/components/CustomerPortal/FrontPageCard'
import { mapGetters } from 'vuex'

export default {
  components: {
    FrontPageCard
  },
  computed: {
    ...mapGetters({
      settings: 'hourrecordSettings'
    }),
    endDate() {
      return new Date(this.settings.hourrecordEndDate)
    }
  },
  mounted() {
    this.$store.dispatch('fetchHourrecordSettings')
  },
  methods: {
    openSurvey() {
      window.open('https://html.brunnereichhof.ch/domains/lohnjaeterei.ch/kundenumfrage/', '_blank')
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
