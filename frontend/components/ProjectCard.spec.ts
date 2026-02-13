import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ProjectCard from './ProjectCard.vue'

const IconStub = {
  template: '<i></i>',
  props: ['name', 'size']
}

const NuxtImgStub = {
  template: '<img :src="src" :alt="alt" />',
  props: ['src', 'alt']
}

const defaultProps = {
  title: 'Test Project',
  description: 'This is a test description.',
  tags: ['Vue', 'TypeScript', 'Tailwind'],
  imageUrl: 'test-image.jpg',
  link: 'https://example.com',
  github: 'https://github.com/example'
}

const mountCard = (props = defaultProps) =>
  mount(ProjectCard, {
    props,
    global: {
      components: {
        Icon: IconStub,
        NuxtImg: NuxtImgStub
      }
    }
  })

describe('ProjectCard.vue', () => {
  it('renders project details correctly', () => {
    const wrapper = mountCard()

    expect(wrapper.find('h3').text()).toBe(defaultProps.title)
    expect(wrapper.find('.project-description').text()).toContain(defaultProps.description)
    expect(wrapper.find('img').attributes('src')).toBe(defaultProps.imageUrl)
    expect(wrapper.findAll('a')[0]?.attributes('href')).toBe(defaultProps.github)
    expect(wrapper.findAll('a')[1]?.attributes('href')).toBe(defaultProps.link)
  })

  it('renders all tags when six or fewer are provided', () => {
    const wrapper = mountCard({
      ...defaultProps,
      tags: ['Tag1', 'Tag2', 'Tag3']
    })

    const tags = wrapper.findAll('.tag-pill')
    expect(tags).toHaveLength(3)
    expect(tags[0]?.text()).toBe('Tag1')
    expect(tags[2]?.text()).toBe('Tag3')
    expect(wrapper.text()).not.toContain('more')
  })

  it('shows overflow indicator when tags are more than six', () => {
    const wrapper = mountCard({
      ...defaultProps,
      tags: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']
    })

    const tags = wrapper.findAll('.tag-pill')
    expect(tags).toHaveLength(7)
    expect(wrapper.text()).toContain('+2 more')
  })

  it('does not render links if link props are missing', () => {
    const wrapper = mountCard({
      title: 'No Links Project',
      description: 'Description here',
      tags: ['Test'],
      imageUrl: 'image.png'
    })

    expect(wrapper.findAll('a')).toHaveLength(0)
  })
})
