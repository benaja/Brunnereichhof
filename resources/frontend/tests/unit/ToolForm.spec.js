import { mount } from '@vue/test-utils'
import ToolForm from '@/components/forms/ToolForm'
import { awaitNextTick } from '../utils'

import { localVue, i18n } from '../test-vue-instance'

describe('ToolForm', () => {
  it('it validates the tool form', async () => {
    const wrapper = mount(ToolForm, {
      propsData: {
        value: {}
      },
      localVue,
      i18n
    }, { localVue, i18n })

    wrapper.vm.validate()

    await awaitNextTick()


    expect(wrapper.find('.v-messages__message').text()).toBe('Dieses Feld muss vorhanden sein')
  })
})
