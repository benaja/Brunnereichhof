<template>
  <div class="container">
    <v-expansion-panels>
      <v-expansion-panel v-if="$auth.user().hasPermission(['superadmin'], ['timerecord_stats'])">
        <v-expansion-panel-header>
          <p class="header-text mb-1">
            <v-icon class="account-icon mr-2">perm_identity</v-icon>Hofmitarbeiter
          </p>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <div>
            <v-divider></v-divider>
            <hour-records-worker></hour-records-worker>
          </div>
        </v-expansion-panel-content>
      </v-expansion-panel>
      <v-expansion-panel v-if="$auth.user().hasPermission(['superadmin'], ['evaluation_employee'])">
        <v-expansion-panel-header>
          <p class="header-text mb-1">
            <v-icon class="account-icon mr-2">account_circle</v-icon>Mitarbeiter
          </p>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <div>
            <v-divider></v-divider>
            <employee-month-rapport></employee-month-rapport>
            <employee-year-rapport></employee-year-rapport>
            <employee-month-total></employee-month-total>
            <employee-list></employee-list>
          </div>
        </v-expansion-panel-content>
      </v-expansion-panel>
      <v-expansion-panel v-if="$auth.user().hasPermission(['superadmin'], ['evaluation_customer'])">
        <v-expansion-panel-header>
          <p class="header-text mb-1">
            <v-icon class="account-icon mr-2">supervisor_account</v-icon>Kunde
          </p>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <div>
            <v-divider></v-divider>
            <customer-week-rapport></customer-week-rapport>
            <customer-year-rapport></customer-year-rapport>
          </div>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <!-- <evaluation-component :evaluations="evaluations"></evaluation-component> -->
  </div>
</template>

<script>
import HourRecordsWorker from '@/components/Evaluation/HourRecordsWorker'
import EmployeeYearRapport from '@/components/Evaluation/EmployeeYearRapport'
import EmployeeMonthRapport from '@/components/Evaluation/EmployeeMonthRapport'
import EmployeeList from '@/components/Evaluation/EmployeeList'
import CustomerYearRapport from '@/components/Evaluation/CustomerYearRapport'
import CustomerWeekRapport from '@/components/Evaluation/CustomerWeekRapport'
import EmployeeMonthTotal from '@/components/Evaluation/EmployeeMonthTotal'
import EvaluationComponent from '@/components/Evaluation/EvaluationComponent'
import { EVALUATION_INPUT_TYPES } from '@/constants'

export default {
  name: 'Evaluation',
  components: {
    HourRecordsWorker,
    EmployeeYearRapport,
    EmployeeMonthRapport,
    EmployeeList,
    CustomerWeekRapport,
    CustomerYearRapport,
    EmployeeMonthTotal,
    EvaluationComponent
  },
  data() {
    return {
      evaluations: [
        {
          inputFiels: [
            {
              key: 'worker',
              dispatch: 'workers',
              label: 'Hofmitarbeier',
              type: EVALUATION_INPUT_TYPES.COMBOBOX
            },
            {
              key: 'month',
              type: EVALUATION_INPUT_TYPES.MONTH_PICKER
            }
          ],
          url: 'worker/{worker}/month/{month}'
        }
      ]
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
