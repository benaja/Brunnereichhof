import Vue from 'vue'
import moment from 'moment'

Vue.filter('date', value => moment(value).format('DD.MM.YYYY'))

Vue.filter('round', value => Math.round(value * 100) / 100)
