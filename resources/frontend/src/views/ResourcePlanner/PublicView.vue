<template>
  <fragment>
    <navigation-bar
      :title="$t('Einsatzplan')"
      :without-nav="!$auth.user()"
      :lading="isLoading"
      mobile-title-below
    >
      <div class="ml-auto d-none d-sm-block">
        <select-day
          v-model="date"
        ></select-day>
      </div>
    </navigation-bar>

    <v-container>
      <div class="d-sm-none d-flex justify-center">
        <select-day
          v-model="date"
        ></select-day>
      </div>
      <div
        v-for="resource of resources"
        :key="resource.id"
        class="pa-4 white mb-8 customer-container"
      >
        <h2>{{ resource.customer.lastname }} {{ resource.customer.firstname }}</h2>

        <p class="mb-1">
          {{ $t('Startzeit') }}: {{ $moment(resource.start_time, 'HH:mm:ss').format('HH:mm') }},
          {{ $t('Endzeit') }}: {{ $moment(resource.end_time, 'HH:mm:ss').format('HH:mm') }}
        </p>

        <p
          v-if="resource.comment"
          class="mb-1"
        >
          {{ $t('Kommentar') }}: {{ resource.comment }}
        </p>

        <v-row>
          <v-col
            cols="12"
            md="8"
          >
            <p class="font-weight-bold mb-1">
              Mitarbeiter
            </p>
            <div
              v-for="(rapportdetail, i) of resource.rapportdetails"
              :key="'r' + rapportdetail.id"
            >
              <v-divider v-if="i !== 0"></v-divider>
              <div class="my-2">
                <div class="d-flex justify-space-between">
                  <p class="mb-0">
                    {{ rapportdetail.employee.lastname }} {{ rapportdetail.employee.firstname }}
                  </p>
                  <p class="mb-0">
                    {{ rapportdetail.hours }} h
                  </p>
                </div>
                <p
                  v-if="rapportdetail.project && rapportdetail.project.name !== 'Allgemein'"
                  class="font-italic"
                >
                  {{ $t('Projekt') }}:{{ rapportdetail.project.name }}
                </p>
              </div>
            </div>
          </v-col>
          <v-col
            cols="12"
            md="4"
          >
            <p class="font-weight-bold">
              Autos
            </p>

            <div
              v-for="(car, i) of resource.cars"
              :key="'c' + car.id"
            >
              <v-divider v-if="i !== 0"></v-divider>
              <div class="my-2">
                <div
                  class="d-flex align-center justify-space-between"
                >
                  <p class="mb-0">
                    {{ car.name }}
                  </p>

                  <div class="avatar-container ml-2">
                    <avatar-image
                      :avatar="car.small_image_url"
                      :image="car.image_url"
                    ></avatar-image>
                  </div>
                </div>
                <p
                  v-if="car.comment"
                  class="mt-1 mb-0 font-italic"
                >
                  {{ car.comment }}
                </p>
                <p
                  v-if="car.important_comment"
                  class="red--text mt-1 mb-0 font-italic"
                >
                  {{ car.important_comment }}
                </p>
              </div>
            </div>

            <p class="font-weight-bold mt-6">
              Werkzeuge
            </p>

            <div
              v-for="(tool, i) of resource.tools"
              :key="'t' + tool.id"
            >
              <v-divider v-if="i !== 0"></v-divider>
              <div class="my-2">
                <div
                  class="d-flex align-center justify-space-between"
                >
                  <p class="mb-0">
                    {{ tool.name }}
                  </p>

                  <div class="ml-2">
                    {{ tool.pivot.amount }} stk.
                  </div>
                </div>
              </div>
            </div>
          </v-col>
        </v-row>
      </div>
    </v-container>
  </fragment>
</template>

<script>
import SelectDay from '@/components/ResourcePlanner/SelectDay'
import AvatarImage from '@/components/ResourcePlanner/plan/AvatarImage'

export default {
  components: {
    SelectDay,
    AvatarImage
  },

  data() {
    return {
      date: this.getCurrentDate(),
      resources: [],
      plannerDay: null,
      isLoading: false
    }
  },

  watch: {
    date() {
      this.fetchResources()
    }
  },

  mounted() {
    this.fetchResources()
  },

  methods: {
    getCurrentDate() {
      const checkTime = this.$moment().hours(17).minutes(0).seconds(0)

      if (this.$moment().isAfter(checkTime)) {
        return this.$moment().add(1, 'days').format('YYYY-MM-DD')
      }
      return this.$moment().format('YYYY-MM-DD')
    },

    async fetchResources() {
      this.isLoading = true
      const { data } = await this.axios.$get('resources', { params: { date: this.date } })
      this.resources = data.resources
      this.plannerDay = data
      this.isLoading = false
    }
  }
}


</script>

<style lang="scss" scoped>
.avatar-container {
  width: 30px;
}

.customer-container {
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}
</style>
