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

  it('displays the copyright text', () => {
    const wrapper = mount(AppFooter, {
      global: {
        components: {
          NuxtLink: NuxtLinkStub,
          Icon: IconStub
        }
      }
    })
    expect(wrapper.text()).toContain('© 2025 iDM Portfolio')
  })

  // Add more tests later for social links, designer link, etc.
})
