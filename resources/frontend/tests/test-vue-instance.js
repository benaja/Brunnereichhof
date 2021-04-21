import { createLocalVue } from '@vue/test-utils'
import VueI18n from 'vue-i18n'
import de from '@/plugins/i18n/de'


// Use a local vue
const localVue = createLocalVue()

localVue.use(VueI18n)

const i18n = new VueI18n({
  locale: 'de',
  messages: {
    de
  }
})


// Create new i18n instance
module.exports = {
  localVue,
  i18n
}
