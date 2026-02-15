<template>
  <section class="testimonials-section my-12 px-4 md:px-6 max-w-(--breakpoint-xl) mx-auto" :data-aos="$nuxt.isHydrating ? undefined : 'fade-up'">
    <div class="mb-6 w-full text-center md:text-left">
      <h2 class="text-2xl md:text-3xl font-extrabold">What Collaborators Say</h2>
      <SectionDivider/>
      <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 max-w-3xl">
        Notes from engineers, founders, and designers I&apos;ve partnered with across product, platform, and mobile delivery.
      </p>
    </div>

    <Splide
      :options="{
        type: 'loop',
        perPage: 2,
        perMove: 1,
        focus: 0,
        gap: '1rem',
        padding: '0',
        arrows: true,
        pagination: true,
        autoplay: true,
        interval: 6500,
        pauseOnHover: true,
        pauseOnFocus: true,
        drag: true,
        snap: true,
        trimSpace: true,
        breakpoints: {
          1024: {
            perPage: 2,
            padding: '0'
          },
          768: {
            perPage: 1,
            padding: '0'
          },
          640: {
            perPage: 1,
            padding: '0'
          }
        }
      }"
      aria-label="Testimonials"
    >
      <SplideSlide
        v-for="(testimonial, index) in testimonials"
        :key="index"
        class="p-2 md:p-4"
        :aria-label="`Testimonial ${index + 1} of ${testimonials.length}`"
      >
        <TestimonialCard v-bind="testimonial" />
      </SplideSlide>
    </Splide>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
// @ts-expect-error IDE couldn't resolve the types
import { Splide, SplideSlide } from '@splidejs/vue-splide'
// @ts-expect-error IDE couldn't resolve the types
import '@splidejs/splide/css'

import TestimonialCard from './TestimonialCard.vue'
import SectionDivider from './SectionDivider.vue'

interface Testimonial {
  name: string
  role: string
  message: string
  avatar?: string
  source?: 'linkedin' | 'X' | 'email'
  url?: string
}

const testimonials = ref<Testimonial[]>([
  {
    name: 'David Ovienmhada',
    role: 'Founder and CEO, Aeeiee Inc., Atlanta, GA',
    message: `Damilola is an inquisitive and thorough Full Stack Software Engineer. He is particularly proficient in iOS Development with Swift, UIKit, and Swift UI. He would make a great addition to your team!`,
    source: 'linkedin',
    avatar: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771013219/Testimonials%20Images/David%20Ovienmhada.jpg',
    url: 'https://www.linkedin.com/in/ovienmhada'
  },
  {
    name: 'Omkar Totade',
    role: 'Senior Software Engineer, Aeeiee Inc., Atlanta, GA',
    message: `I had the pleasure of working with Damilola from 2020 until late last year, and during that time, he consistently impressed me with his adaptability, technical skills, and team-first mindset. We collaborated on a range of projects—from web development and custom WordPress plugins to building full websites using the Gutenberg editor. Most recently, Damilola played a key role on my iOS team in building Catch the Ghost, our company’s flagship productivity app.

What stands out about Damilola is his ability to quickly pick up new technologies and start contributing right away. When he joined us in 2020, he hit the ground running by developing a critical WordPress plugin for a high-impact project. Later, even with limited experience in iOS development, he eagerly transitioned to our app team and was instrumental in implementing key features that streamlined user onboarding.

Damilola is not only a talented and fast-learning engineer, but also a fantastic teammate—always positive, collaborative, and open to tackling new challenges. I highly recommend him for any software engineering role.`,
    source: 'linkedin',
    avatar: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771013182/Testimonials%20Images/Omkar%20Totade.jpg',
    url: 'https://www.linkedin.com/in/omkartotade'
  },
  {
    name: 'Oloruntobi Awoderu',
    role: 'Fullstack Engineer, Veratrak, UK',
    message: `I had the pleasure of working closely with Damilola on several key projects, and I can confidently say that he is one of the most skilled and dedicated professionals I've ever collaborated with. His deep technical expertise, coupled with his ability to think strategically, made a significant impact on our team's success.

Damilola excels in problem-solving and has an impressive ability to quickly grasp complex concepts and turn them into actionable solutions. His contributions to our projects were invaluable, particularly as a skilled IOS developer, where his knowledge and commitment consistently exceeded expectations.

Beyond his technical skills, Damilola is a fantastic team player. He communicates, is always willing to lend a hand, and provides thoughtful insights that elevate team discussions. His collaborative approach fosters a positive and productive environment, and his leadership qualities shine through every interaction.

I highly recommend Damilola for any role he chooses to pursue. His work ethic, technical acumen, and ability to drive results make him a standout professional; any team would be lucky to have him.`,
    source: 'linkedin',
    avatar: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771013125/Testimonials%20Images/Oloruntobi%20Awoderu.jpg',
    url: 'https://www.linkedin.com/in/oloruntobiawoderu/'
  },
  {
    name: 'Adekemi Borode',
    role: 'Lead UI/UX Designer, Aeeiee Inc., Atlanta, GA',
    message: 'I worked with Damilola for a few years and can confidently say he’s an excellent software engineer. He consistently delivers high-quality work and meets deadlines. His technical expertise is solid, and he’s always reliable in completing tasks. One of the things that stands him out is how approachable and easy he is to work with—his friendly and collaborative nature makes any team better. He’s someone you would want on your team or project.',
    source: 'linkedin',
    avatar: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771013227/Testimonials%20Images/Adekemi%20Borode.jpg',
    url: 'https://www.linkedin.com/in/adekemiborode/'
  },
  {
    name: 'Anesi Igebu',
    role: 'Founder, Kudazi Roots',
    message: `Working with Damilola is as seamless as it gets. He's one of the best software engineers I have been lucky to work with because his communication is top notch and has an excellent work ethic!

I had the privilege of working alongside Damilola on the iOS team, where his contributions were critical to the successful development and growth of Catch the Ghost, a groundbreaking productivity app. From day one, Damilola demonstrated an exceptional mastery of iOS development, consistently delivering clean, efficient code and proactively tackling complex challenges. 

Beyond his technical expertise, Damilola brought a collaborative mindset to the team. He was always willing to share his knowledge, mentor junior developers, and offer innovative solutions that elevated the overall project. His ability to understand both the big picture and the finer details allowed him to bridge the gap between product vision and practical implementation, ensuring that the app not only met but exceeded user expectations.

His dedication to the project extended beyond coding; he played a key role in brainstorming sessions and contributed valuable insights that improved the app’s functionality and user engagement. His forward-thinking approach helped us anticipate user needs and stay ahead of potential challenges, ultimately driving the app’s success and increasing its adoption.

In short, Damilola is not only a highly skilled iOS developer but also a team player who adds significant value to any project he’s a part of. His technical expertise, creative problem-solving, and collaborative nature make him an asset to any team. I highly recommend him for any future endeavours.`,
    source: 'linkedin',
    avatar: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771013380/Testimonials%20Images/Anesi%20Igebu.jpg',
    url: 'https://www.linkedin.com/in/anesi-igebu/'
  },
  {
    name: 'Itunu R.',
    role: 'iOS Engineer, Verse Communications PBC',
    message: `As a fullstack engineer, Damilola demonstrates a solid grasp of both frontend and backend technologies, consistently delivering clean and efficient code. He has expanded his skills into iOS development, showing a keen ability to adapt to new platforms.

What stands out about Damilola is his eagerness for quickly mastering new technologies. This adaptability, combined with his communication skills, makes him a great teammate. His strong foundation in fullstack development, coupled with his growing expertise in mobile development, positions him as a versatile engineer who can contribute significantly to a wide range of projects, and any organization would be fortunate to have him on their team.`,
    source: 'linkedin',
    avatar: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771013386/Testimonials%20Images/Itunu%20Raimi.jpg',
    url: 'https://www.linkedin.com/in/itunuraimi/'
  },
  {
    name: 'Gabriel Osuobiem',
    role: 'Software Engineer, Aeeiee Inc., Atlanta, GA',
    message: `I had the pleasure of working with Damilola at Aeeiee, and I can confidently say he is an outstanding full-stack and iOS engineer. His technical expertise, combined with his ability to approach challenges strategically, played a critical role in the success of our projects.

Damilola consistently excelled in problem-solving and swiftly transformed complex ideas into effective solutions. His expertise in iOS development was especially notable, and his contributions were crucial to the success of many of our projects.

What sets Damilola apart is not just his technical ability but his collaborative mindset. He’s always willing to share his knowledge, offer valuable insights, and mentor others. His forward-thinking approach often helped us anticipate and overcome challenges early, ensuring project success.

Damilola’s combination of technical expertise, problem-solving skills, and team-first attitude makes him an invaluable asset to any team. I highly recommend him for future endeavors, as I am confident he will excel in any role he takes on.`,
    source: 'linkedin',
    avatar: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771013016/Testimonials%20Images/Gabriel%20Osuobiem.jpg',
    url: 'https://www.linkedin.com/in/osuobiem/'
  },
])
</script>

<style>
.testimonials-section .splide__track {
  padding: 1.5rem 0;
  margin: 0;
}

.testimonials-section .splide__list {
  align-items: stretch;
  padding: 1rem 0;
}

.testimonials-section .splide__slide {
  display: flex;
  height: auto;
}

.testimonials-section .splide__slide .testimonial-card {
  width: 100%;
  height: 100%;
  opacity: 0.82;
  transition: opacity 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
}

@media (min-width: 768px) {
  .testimonials-section .splide__slide {
    padding: 1rem;
  }
}

.testimonials-section .splide__slide.is-active .testimonial-card {
  opacity: 1;
  box-shadow: 0 10px 30px rgb(0 0 0 / 0.2);
}

.testimonials-section .splide__arrow {
  background: rgb(255 255 255);
  opacity: 1;
  width: 2.5rem;
  height: 2.5rem;
  border: 1px solid rgb(229 231 235);
  box-shadow: 0 2px 8px rgb(0 0 0 / 0.12);
}

.testimonials-section .splide__arrow svg {
  fill: #f54747;
  width: 1em;
}

.testimonials-section .splide__arrow:hover {
  background: #f54747;
}

.testimonials-section .splide__arrow:hover svg {
  fill: white;
}

.testimonials-section .splide__pagination {
  bottom: -2rem;
}

.testimonials-section .splide__pagination__page {
  background: #e5e7eb;
  margin: 0 4px;
  width: 0.5rem;
  height: 0.5rem;
}

.testimonials-section .splide__pagination__page.is-active {
  background: #f54747;
  transform: scale(1.2);
}

.dark .testimonials-section .splide__arrow {
  background: rgb(24 24 27);
  border-color: rgb(63 63 70);
}

@media (max-width: 640px) {
  .testimonials-section .splide__track {
    padding: 0.75rem 0 1rem;
  }

  .testimonials-section .splide__arrow {
    width: 2rem;
    height: 2rem;
    opacity: 0.7;
    background: rgba(255, 255, 255, 0.8);
  }

  .testimonials-section .splide__arrow svg {
    width: 0.8em;
  }

  .testimonials-section .splide__pagination {
    bottom: -1.5rem;
  }

  .testimonials-section .splide__pagination__page {
    width: 0.4rem;
    height: 0.4rem;
    margin: 0 3px;
    opacity: 0.7;
  }
}
</style>
