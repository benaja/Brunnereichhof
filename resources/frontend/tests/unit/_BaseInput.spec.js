import { shallowMount, mount } from '@vue/test-utils'
import _BaseInput from '@/components/FormComponents/_BaseInput'
import { awaitNextTick } from '../utils'

describe('BaseInput', () => {
  it('it renders the label', () => {
    const label = 'new message'
    const wrapper = shallowMount(_BaseInput, {
      propsData: { label }
    })

    expect(wrapper.find('p').text()).toBe(label)
  })

  it('it renders the resore button', () => {
    const restoreMessage = 'Restore original value'
    const wrapper = mount(_BaseInput, {
      propsData: {
        restoreMessage
      }
    })

    expect(wrapper.get('button'))
  })

  it('it renders the restore tooltip on hover', async () => {
    const restoreMessage = 'Restore original value'
    const wrapper = mount(_BaseInput, {
      propsData: {
        restoreMessage
      }
    })

    const button = wrapper.get('button')
    button.trigger('mouseenter')
    await awaitNextTick()

    expect(wrapper.vm.$refs.restoreMessage.textContent).toContain(restoreMessage)
  })
})
