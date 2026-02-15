import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import AppFooter from './AppFooter.vue'

// Mock the NuxtLink and Icon components
const NuxtLinkStub = {
  template: '<a :href="to"><slot /></a>',
  props: ['to']
}

const IconStub = {
  template: '<i></i>', // Simple placeholder for Icon
  props: ['name', 'size']
}

describe('AppFooter.vue', () => {
  it('renders correctly', () => {
    const wrapper = mount(AppFooter, {
      global: {
        components: {
          NuxtLink: NuxtLinkStub,
          Icon: IconStub
        }
      }
    })
    expect(wrapper.exists()).toBe(true)
  })

  it('displays current year copyright text', () => {
    const wrapper = mount(AppFooter, {
      global: {
        components: {
          NuxtLink: NuxtLinkStub,
          Icon: IconStub
        }
      }
    })
    const currentYear = new Date().getFullYear()
    expect(wrapper.text()).toContain(`© ${currentYear} iDM Portfolio`)
  })

  it('renders primary navigation links', () => {
    const wrapper = mount(AppFooter, {
      global: {
        components: {
          NuxtLink: NuxtLinkStub,
          Icon: IconStub
        }
      }
    })

    expect(wrapper.text()).toContain('About')
    expect(wrapper.text()).toContain('Projects')
    expect(wrapper.text()).toContain('Credentials')
    expect(wrapper.text()).toContain('Contact')
  })
})
