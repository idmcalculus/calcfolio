import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ProjectCard from './ProjectCard.vue'

// Mock the Icon component as it's likely globally registered or external
const Icon = {
  template: '<i></i>' // Simple mock template
}

describe('ProjectCard.vue', () => {
  const defaultProps = {
    title: 'Test Project',
    description: 'This is a test description.',
    tags: ['Vue', 'TypeScript', 'Tailwind'],
    image: 'test-image.jpg',
    link: 'https://example.com',
    github: 'https://github.com/example'
  }

  it('renders project details correctly', () => {
    const wrapper = mount(ProjectCard, {
      props: defaultProps,
      global: {
        components: {
          Icon // Register the mock Icon component
        }
      }
    })

    expect(wrapper.find('h3').text()).toBe(defaultProps.title)
    expect(wrapper.find('p').text()).toBe(defaultProps.description)
    expect(wrapper.find('img').attributes('src')).toBe(defaultProps.image)
    expect(wrapper.findAll('a')[0].attributes('href')).toBe(defaultProps.github)
    expect(wrapper.findAll('a')[1].attributes('href')).toBe(defaultProps.link)
  })

  it('renders tags with separators correctly', () => {
    const wrapper = mount(ProjectCard, {
      props: {
        ...defaultProps,
        tags: ['Tag1', 'Tag2', 'Tag3']
      },
      global: {
        components: {
          Icon
        }
      }
    })

    const tagsContainer = wrapper.find('.flex.flex-wrap.gap-2.mb-3')
    // Check the text content of the container, trimming whitespace
    const renderedText = tagsContainer.text().replace(/\s+/g, ' ').trim()
    expect(renderedText).toBe('Tag1 - Tag2 - Tag3')
  })

  it('renders a single tag without a separator', () => {
    const wrapper = mount(ProjectCard, {
      props: {
        ...defaultProps,
        tags: ['SingleTag']
      },
      global: {
        components: {
          Icon
        }
      }
    })

    const tagsContainer = wrapper.find('.flex.flex-wrap.gap-2.mb-3')
    const renderedText = tagsContainer.text().replace(/\s+/g, ' ').trim()
    expect(renderedText).toBe('SingleTag')
  })

  it('renders no tags when the tags array is empty', () => {
    const wrapper = mount(ProjectCard, {
      props: {
        ...defaultProps,
        tags: []
      },
      global: {
        components: {
          Icon
        }
      }
    })

    const tagsContainer = wrapper.find('.flex.flex-wrap.gap-2.mb-3')
    expect(tagsContainer.exists()).toBe(true) // Container exists
    expect(tagsContainer.findAll('span').length).toBe(0) // No span elements inside
    expect(tagsContainer.text().trim()).toBe('') // No text content
  })

  it('does not render links if props are not provided', () => {
    const wrapper = mount(ProjectCard, {
      props: {
        title: 'No Links Project',
        description: 'Description here',
        tags: ['Test'],
        image: 'image.png'
        // link and github props omitted
      },
      global: {
        components: {
          Icon
        }
      }
    })

    // Expect only 0 'a' tags if link and github are undefined
    expect(wrapper.findAll('a').length).toBe(0)
  })
})
