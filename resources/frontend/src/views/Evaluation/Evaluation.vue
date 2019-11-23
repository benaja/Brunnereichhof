<template>
  <div class="container">
    <evaluation-component :evaluation-groups="evaluationGroups"></evaluation-component>
  </div>
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
              url: 'pdf/worker/{worker}/month/{month}',
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
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'year',
                  type: EVALUATION_INPUT_TYPES.YEAR_PICKER
                }
              ],
              url: 'worker/{worker}/year/{year}',
              rules: {
                year: v => !!v
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
                  key: 'month',
                  type: EVALUATION_INPUT_TYPES.MONTH_PICKER
                }
              ],
              url: 'pdf/employee/month/{month}',
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
              url: 'pdf/employee/{employee}/year/{year}',
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
              url: 'employee/{employee}/evaluation/{date}',
              rules: {
                date: v => !!v,
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
              url: 'pdf/customer/{customer}/week/{date}',
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
                  selectAll: true,
                  type: EVALUATION_INPUT_TYPES.COMBOBOX
                },
                {
                  key: 'year',
                  type: EVALUATION_INPUT_TYPES.YEAR_PICKER
                }
              ],
              url: 'pdf/customer/{customer}/year/{year}',
              rules: {
                year: v => !!v
              }
            }
          ]
        }
      ]
    }
  }
}
</script>

<style lang="scss" scoped>
</style>
