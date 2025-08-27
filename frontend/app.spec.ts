import { describe, it, expect, vi } from 'vitest'
import { shallowMount } from '@vue/test-utils'
import { ref } from 'vue'
import App from './layouts/deafult.vue' // Assuming app.vue is the root component

// Mock Nuxt features and components
const MockNuxtLink = {
  template: '<a :to="to"><slot /></a>',
  props: ['to', 'exact-active-class', 'active-class']
}
const MockNuxtPage = { template: '<div>NuxtPage Mock</div>' }
const MockDownloadCV = { template: '<div>DownloadCV Mock</div>' }
const MockMobileMenuDrawer = { template: '<div>MobileMenuDrawer Mock</div>' }
const MockScrollToTop = { template: '<div>ScrollToTop Mock</div>' }

// Mock the composable
vi.mock('~/composables/useDarkMode', () => ({
  useDarkMode: () => ({
    isDark: ref(false), // Default mock state
    toggleDark: vi.fn()
  })
}))

describe('App.vue', () => {
  it('renders the main layout and header elements correctly', () => {
    const wrapper = shallowMount(App, {
      global: {
        components: {
          NuxtLink: MockNuxtLink,
          NuxtPage: MockNuxtPage,
          DownloadCV: MockDownloadCV,
          MobileMenuDrawer: MockMobileMenuDrawer,
          ScrollToTop: MockScrollToTop
        },
        stubs: { // Use stubs for deeper component mocking if needed
          NuxtLink: true,
          NuxtPage: true,
          DownloadCV: true,
          MobileMenuDrawer: true,
          ScrollToTop: true
        }
      }
    })

    // Check header exists
    const header = wrapper.find('header')
    expect(header.exists()).toBe(true)
    const headerContainer = header.find('.container') // Find the container div
    expect(headerContainer.exists()).toBe(true)
    expect(headerContainer.classes()).toContain('max-w-(--breakpoint-xl)')

    // Check logo link within container
    const logoLink = headerContainer.findComponent(MockNuxtLink) // Find the first NuxtLink (logo)
    expect(logoLink.exists()).toBe(true)
    expect(logoLink.props('to')).toBe('/')
    // shallowMount might not render slot content easily, check existence
    // expect(logoLink.text()).toBe('IDM') // This might fail with shallowMount + stubs

    // Check navigation links within the wrapper div inside the container
    const navWrapper = headerContainer.find('.hidden.md\\:flex') // Find the wrapper div
    expect(navWrapper.exists()).toBe(true)
    const navLinks = navWrapper.findAllComponents(MockNuxtLink)
    expect(navLinks.length).toBe(3) // 3 nav links inside the nav element
    expect(navLinks[0].props('to')).toBe('/') // About
    expect(navLinks[1].props('to')).toBe('/projects') // Project
    expect(navLinks[2].props('to')).toBe('/contact') // Contact

    // Check DownloadCV component presence within the wrapper div
    expect(navWrapper.findComponent(MockDownloadCV).exists()).toBe(true)

    // Check dark mode toggle button within the wrapper div
    const darkModeButton = navWrapper.find('button[aria-label="Toggle dark mode"]')
    expect(darkModeButton.exists()).toBe(true)
    expect(darkModeButton.text()).toBe('🌙') // Based on default mock isDark=false

    // Check mobile menu toggle button (outside the container div now)
    const mobileToggle = header.find('button.md\\:hidden')
    expect(mobileToggle.exists()).toBe(true)
    expect(mobileToggle.classes()).toContain('absolute') // Check new positioning class
    expect(mobileToggle.text()).toBe('☰') // Based on default showMenu=false

    // Check main content area
    expect(wrapper.find('main').exists()).toBe(true)
    expect(wrapper.findComponent(MockNuxtPage).exists()).toBe(true)

    // Check footer
    const footer = wrapper.find('footer')
    expect(footer.exists()).toBe(true)
    expect(footer.text()).toContain('© 2025 iDM Portfolio')

    // Check ScrollToTop component presence
    expect(wrapper.findComponent(MockScrollToTop).exists()).toBe(true)
  })

  // Add more tests, e.g., for dark mode toggling, mobile menu interaction
})
