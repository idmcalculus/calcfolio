import { describe, it, expect } from 'vitest'
import { mountSuspended } from '@nuxt/test-utils/runtime'
import HeroSection from './HeroSection.vue'

// Mock the Icon component as it's likely globally registered or auto-imported
const MockIcon = {
  template: '<span :name="name" :size="size">IconMock</span>',
  props: ['name', 'size']
}

describe('HeroSection', () => {
  it('renders correctly with expected content and structure', async () => {
    const wrapper = await mountSuspended(HeroSection, {
      global: {
        components: {
          Icon: MockIcon // Provide the mock
        }
      }
    })

    // Check section max-width
    const section = wrapper.find('section.hero-section')
    expect(section.classes()).toContain('max-w-screen-xl')

    // Check main heading text
    const heading = wrapper.find('h1')
    expect(heading.exists()).toBe(true)
    expect(heading.text()).toContain('Damilola Michael Ige')

    // Check for gradient span
    const gradientSpan = heading.find('span.text-gradient')
    expect(gradientSpan.exists()).toBe(true)
    expect(gradientSpan.text()).toBe('Damilola Michael Ige')

    // Check introductory paragraph
    expect(wrapper.text()).toContain("Hello, I'm")
    expect(wrapper.text()).toContain('software engineer based in Toronto')

    // Check red underline div
    expect(wrapper.find('div.bg-red-500').exists()).toBe(true)

    // Check profile image
    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('/images/profile.jpg')
    expect(img.attributes('alt')).toBe('Damilola Michael Ige')
    expect(img.classes()).toContain('relative') // Check for relative positioning
    expect(img.classes()).toContain('-mt-20') // Check for negative top margin

    // Check image wrapper for gradient and size
    const imgWrapper = wrapper.find('div.bg-gradient-to-br')
    expect(imgWrapper.exists()).toBe(true)
    expect(imgWrapper.classes()).toContain('w-64') // Check wrapper width
    expect(imgWrapper.classes()).toContain('h-64') // Check wrapper height
    expect(imgWrapper.classes()).toContain('md:w-80') // Check responsive wrapper width
    expect(imgWrapper.classes()).toContain('md:h-80') // Check responsive wrapper height
    expect(imgWrapper.classes()).not.toContain('overflow-hidden') // Ensure overflow is not hidden

    // Check parent column for padding
    const rightColumn = wrapper.find('.md\\:w-1\\/2.flex.flex-col')
    expect(rightColumn.classes()).toContain('pt-20') // Check for padding-top

    // Check social links container
    const socialLinksContainer = wrapper.find('div.flex.gap-5')
    expect(socialLinksContainer.classes()).toContain('mt-8') // Check margin-top
    const socialLinks = socialLinksContainer.findAll('a')
    expect(socialLinks.length).toBe(4) // Expecting 4 social links

    // Check individual social icons (using mocked component's attributes)
    const icons = wrapper.findAllComponents(MockIcon)
    expect(icons.length).toBe(4)
    expect(icons[0].props('name')).toBe('simple-icons:github')
    expect(icons[1].props('name')).toBe('simple-icons:x')
    expect(icons[2].props('name')).toBe('simple-icons:instagram')
    expect(icons[3].props('name')).toBe('simple-icons:linkedin')
    expect(icons[0].props('size')).toBe('28')
  })
})
