<template>
  <fragment>
    <navigation-bar title="Auswertung"></navigation-bar>
    <v-container>
      <evaluation-component :evaluation-groups="evaluationGroups"></evaluation-component>
    </v-container>
  </fragment>
</template>

<script>
import EvaluationComponent from '@/components/Evaluation/EvaluationComponent'
import { EVALUATION_INPUT_TYPES } from '@/constants'

export default {
  name: 'Evaluation',
  components: {
    EvaluationComponent
  },
  data() {
    return {
      evaluationGroups: [
        {
          name: 'Hofmitarbeiter',
          icon: 'perm_identity',
          show: this.$auth.user().hasPermission(['superadmin'], ['timerecord_stats']),
          evaluations: [
            {
              title: 'Stundenangaben',
              inputFields: [
                {
                  key: 'worker',
                  dispatch: 'workers',
                  label: 'Hofmitarbeier',
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'month',
                  type: EVALUATION_INPUT_TYPES.MONTH_PICKER
                }
              ],
              url: 'pdf/timerecords/workers/{worker}?month={month}',
              rules: {
                month: v => !!v
              }
            },
            {
              title: 'Jahresrapport',
              inputFields: [
                {
                  key: 'worker',
                  dispatch: 'workers',
                  label: 'Hofmitarbeier',
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'year',
                  type: EVALUATION_INPUT_TYPES.YEAR_PICKER
                }
              ],
              url: 'pdf/timerecords/workers/{worker}?year={year}',
              rules: {
                year: v => !!v
              }
            },
            {
              title: 'Verpflegungen',
              inputFields: [
                {
                  key: 'date',
                  type: EVALUATION_INPUT_TYPES.MONTH_OR_YEAR_PICKER
                }
              ],
              url: 'pdf/timerecords/meals?{date}',
              rules: {
                date: v => !!v
              }
            }
          ]
        },
        {
          name: 'Mitarbeiter',
          icon: 'account_circle',
          show: this.$auth.user().hasPermission(['superadmin'], ['evaluation_employee']),
          evaluations: [
            {
              title: 'Monatsrapport',
              inputFields: [
                {
                  key: 'employee',
                  dispatch: 'employees',
                  getter: 'activeEmployees',
                  label: 'Mitarbeiter',
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'month',
                  type: EVALUATION_INPUT_TYPES.MONTH_PICKER
                }
              ],
              url: 'pdf/employees/month-rapport?date={month}&employeeId={employee}',
              rules: {
                month: v => !!v
              }
            },
            {
              title: 'Jahresrapport',
              inputFields: [
                {
                  key: 'employee',
                  dispatch: 'employees',
                  label: 'Mitarbeiter',
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'year',
                  type: EVALUATION_INPUT_TYPES.YEAR_PICKER
                }
              ],
              url: 'pdf/employees/{employee}/year-rapport?date={year}',
              rules: {
                year: v => !!v,
                employee: v => !!v
              }
            },
            {
              title: 'Tagestotale',
              inputFields: [
                {
                  key: 'employee',
                  dispatch: 'employees',
                  label: 'Mitarbeiter',
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'date',
                  type: EVALUATION_INPUT_TYPES.MONTH_OR_YEAR_PICKER
                }
              ],
              errors: [{
                message: 'Employee has no entries',
                alert: { text: 'Mitarbeiter hat keine Stundenangaben zur gewählten Zeit', type: 'warning' }
              }],
              url: 'pdf/day-total/employees/{employee}?{date}',
              rules: {
                date: v => !!v,
                employee: v => !!v
              }
            },
            {
              title: 'Übernachtungen',
              inputFields: [
                {
                  key: 'employee',
                  dispatch: 'employees',
                  getter: 'employeesWithGuests',
                  label: 'Mitarbeiter',
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'year',
                  type: EVALUATION_INPUT_TYPES.YEAR_PICKER
                }
              ],
              url: 'pdf/reservations/employees/{employee}?date={year}',
              rules: {
                year: v => !!v,
                employee: v => !!v
              }
            },
            {
              title: 'Übernachtungen pro Mitarbeiter',
              inputFields: [
                {
                  key: 'date',
                  type: EVALUATION_INPUT_TYPES.MONTH_OR_YEAR_PICKER
                }
              ],
              errors: [{
                message: 'Employee has no entries',
                alert: { text: 'Mitarbeiter hat keine Stundenangaben zur gewählten Zeit', type: 'warning' }
              }],
              url: 'pdf/sleep-over/employees?{date}',
              rules: {
                date: v => !!v
              }
            },
            {
              title: 'Verpflegungen auf Eichhof',
              inputFields: [
                {
                  key: 'date',
                  type: EVALUATION_INPUT_TYPES.MONTH_OR_YEAR_PICKER
                }
              ],
              url: 'pdf/foods/employees?{date}',
              rules: {
                date: v => !!v
              }
            },
            {
              title: 'Saldo',
              inputFields: [
                {
                  key: 'employee',
                  dispatch: 'employees',
                  getter: 'activeEmployees',
                  label: 'Mitarbeiter',
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                }
              ],
              url: 'pdf/employees/{employee}/saldo',
              rules: {
                employee: v => !!v
              }
            },
            {
              title: 'Mitarbeiterliste',
              inputFields: [],
              url: 'pdf/employees',
              buttonText: 'Mitarbeiterliste'
            }
          ]
        },
        {
          name: 'Kunde',
          icon: 'supervisor_account',
          show: this.$auth.user().hasPermission(['superadmin'], ['evaluation_customer']),
          evaluations: [
            {
              title: 'Wochenrapport',
              inputFields: [
                {
                  key: 'customer',
                  dispatch: 'customers',
                  label: 'Kunde',
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'date',
                  type: EVALUATION_INPUT_TYPES.DATE_PICKER
                }
              ],
              url: 'pdf/customers/{customer}/week/{date}',
              rules: {
                date: v => !!v
              }
            },
            {
              title: 'Jahresrapport',
              inputFields: [
                {
                  key: 'customer',
                  dispatch: 'customers',
                  label: 'Kunde',
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'year',
                  type: EVALUATION_INPUT_TYPES.YEAR_PICKER
                }
              ],
              url: 'pdf/customers/{customer}/year/{year}',
              rules: {
                year: v => !!v
              }
            },
            {
              title: 'Stundenangaben',
              inputFields: [
                {
                  key: 'customer',
                  dispatch: 'customers',
                  label: 'Kunde',
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'year',
                  type: EVALUATION_INPUT_TYPES.YEAR_PICKER
                }
              ],
              errors: [{
                message: 'No hourrecords for selected time',
                alert: { text: 'Keine Stundenangaben zur ausgewählten Zeit', type: 'warning' }
              }],
              url: 'pdf/hourrecords/{year}/customers/{customer}',
              rules: {
                year: v => !!v
              }
            },
            {
              title: 'Kundenverzeichnis Export',
              inputFields: [],
              url: 'export/customers',
              buttonText: 'Kundenverzeichnis exportieren'
            }
          ]
        }
      ]
    }
  }
}
</script>
