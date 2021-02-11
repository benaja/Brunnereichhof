import { mount } from '@vue/test-utils'
import TextField from '@/components/FormComponents/TextField'
import { awaitNextTick } from '../utils'

describe('Text Field', () => {
  it('it renders the label', () => {
    const label = 'test label'
    const wrapper = mount(TextField, {
      propsData: {
        label
      }
    })

    expect(wrapper.find('p').text()).toBe(label)
  })

  it('it passes the value proppery', () => {
    const value = 'test value'
    const wrapper = mount(TextField, {
      propsData: {
        value
      }
    })

    expect(wrapper.find('input').element.value).toBe(value)
  })

  it('it can restore the original value', async () => {
    const original = 'test value 123'
    const wrapper = mount(TextField, {
      propsData: {
        value: 'random other value',
        original
      }
    })

    wrapper.find('button').trigger('click')

    await awaitNextTick()

    expect(wrapper.emitted().input[0][0]).toBe(original)
  })
})
