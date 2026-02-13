import { describe, it, expect } from 'vitest'
import { mountSuspended } from '@nuxt/test-utils/runtime'
import HeroSection from './HeroSection.vue'

const MockIcon = {
  template: '<span :data-name="name" :data-size="size">IconMock</span>',
  props: ['name', 'size']
}

describe('HeroSection', () => {
  it('renders the updated hero hierarchy and actions', async () => {
    const wrapper = await mountSuspended(HeroSection, {
      global: {
        components: {
          Icon: MockIcon
        }
      }
    })

    const section = wrapper.find('section.hero-section')
    expect(section.classes()).toContain('max-w-(--breakpoint-xl)')

    expect(wrapper.text()).toContain('Damilola Michael Ige')

    const heading = wrapper.find('h1')
    expect(heading.exists()).toBe(true)
    expect(heading.text()).toContain('I engineer dependable products from interface to infrastructure.')

    const typingGreeting = wrapper.find('.typing-text')
    expect(typingGreeting.exists()).toBe(true)

    const ctas = wrapper.findAll('.hero-btn')
    expect(ctas.length).toBe(3)
    expect(ctas[0]?.text()).toContain('View Projects')
    expect(ctas[1]?.text()).toContain('Download CV')
    expect(ctas[2]?.text()).toContain('Contact')

    const experienceLink = wrapper.find('a[href="#experience"]')
    expect(experienceLink.exists()).toBe(true)
    expect(experienceLink.text()).toContain('Explore experience')

    const socialLinks = wrapper.findAll('a[aria-label="GitHub"], a[aria-label="X"], a[aria-label="Instagram"], a[aria-label="LinkedIn"]')
    expect(socialLinks.length).toBe(4)

    const icons = wrapper.findAllComponents(MockIcon)
    const iconNames = icons.map((icon) => icon.props('name'))
    expect(iconNames).toContain('simple-icons:github')
    expect(iconNames).toContain('simple-icons:x')
    expect(iconNames).toContain('simple-icons:instagram')
    expect(iconNames).toContain('simple-icons:linkedin')

    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('/images/profile.svg')
    expect(img.attributes('alt')).toBe('Damilola Michael Ige')
  })
})
