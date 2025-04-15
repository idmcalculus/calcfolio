<template>
  <section class="testimonials-section my-12 px-4 md:px-6 max-w-screen-xl mx-auto" data-aos="fade-up">
    <div class="mb-6 w-full text-center md:text-left">
      <h2 class="text-2xl md:text-3xl font-extrabold">Testimonials</h2>
      <SectionDivider/>
    </div>

    <Splide
      :options="{
        type: 'loop',
        perPage: 3,
        perMove: 1,
        focus: 'center',
        gap: '0.1rem',
        padding: '5%',
        arrows: true,
        pagination: true,
        autoplay: true,
        interval: 5000,
        pauseOnHover: true,
        drag: true,
        snap: true,
        trimSpace: false,
        breakpoints: {
          1024: {
            perPage: 3,
            padding: '5%'
          },
          768: {
            perPage: 2,
            padding: '10%'
          },
          640: {
            perPage: 1,
            padding: '15%'
          }
        }
      }"
      aria-label="Testimonials"
    >
      <SplideSlide
        v-for="(testimonial, index) in testimonials"
        :key="index"
        class="p-4"
      >
        <TestimonialCard v-bind="testimonial" />
      </SplideSlide>
    </Splide>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AOS from 'aos'
import { Splide, SplideSlide } from '@splidejs/vue-splide'
import '@splidejs/splide/css'

import TestimonialCard from './TestimonialCard.vue'
import SectionDivider from './SectionDivider.vue'

onMounted(() => {
  AOS.init({ duration: 800 })
})

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
    message: 'Damilola is an inquisitive and thorough Full Stack Software Engineer. He is particularly proficient in iOS Development with Swift, UIKit, and Swift UI. He would make a great addition to your team!',
    source: 'linkedin',
    avatar: 'https://media.licdn.com/dms/image/v2/C5103AQFVVn-wyfe0tg/profile-displayphoto-shrink_800_800/profile-displayphoto-shrink_800_800/0/1517566998788?e=1749081600&v=beta&t=lLypC8J9jZT9AQTl6hOBKQU7169tPgon-i1yH2cTqqg',
    url: 'https://www.linkedin.com/in/ovienmhada'
  },
  {
    name: 'Oloruntobi Awoderu',
    role: 'Fullstack Engineer, Veratrak, UK',
    message: `I had the pleasure of working closely with Damilola on several key projects, and I can confidently say that he is one of the most skilled and dedicated professionals I've ever collaborated with. His deep technical expertise, coupled with his ability to think strategically, made a significant impact on our team's success.

Damilola excels in problem-solving and has an impressive ability to quickly grasp complex concepts and turn them into actionable solutions. His contributions to our projects were invaluable, particularly as a skilled IOS developer, where his knowledge and commitment consistently exceeded expectations.

Beyond his technical skills, Damilola is a fantastic team player. He communicates, is always willing to lend a hand, and provides thoughtful insights that elevate team discussions. His collaborative approach fosters a positive and productive environment, and his leadership qualities shine through every interaction.

I highly recommend Damilola for any role he chooses to pursue. His work ethic, technical acumen, and ability to drive results make him a standout professional; any team would be lucky to have him.`,
    source: 'linkedin',
    avatar: 'https://media.licdn.com/dms/image/v2/C5603AQH3olw6ZduFZw/profile-displayphoto-shrink_800_800/profile-displayphoto-shrink_800_800/0/1646503267395?e=1749081600&v=beta&t=dTLm0gbdJcYji8BsSNOeK8nWLdqayBN2uTdPM59uNVo',
    url: 'https://www.linkedin.com/in/oloruntobiawoderu/'
  },
  {
    name: 'Adekemi Borode',
    role: 'Lead UI/UX Designer, Aeeiee Inc., Atlanta, GA',
    message: 'I worked with Damilola for a few years and can confidently say he’s an excellent software engineer. He consistently delivers high-quality work and meets deadlines. His technical expertise is solid, and he’s always reliable in completing tasks. One of the things that stands him out is how approachable and easy he is to work with—his friendly and collaborative nature makes any team better. He’s someone you would want on your team or project.',
    source: 'linkedin',
    avatar: 'https://media.licdn.com/dms/image/v2/D4D03AQF7DVlv_qbcxg/profile-displayphoto-shrink_800_800/profile-displayphoto-shrink_800_800/0/1697040829676?e=1749081600&v=beta&t=XjDKbmA_XlpbCEelT8RhhqJXIPfxnu2tIGuLq7vdlPM',
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
    avatar: 'https://media.licdn.com/dms/image/v2/D4D03AQH__ygR48LW2w/profile-displayphoto-shrink_800_800/profile-displayphoto-shrink_800_800/0/1727384832120?e=1749081600&v=beta&t=Tf6nTJs9JlrU47p5q5bQ87Ksx8zdET5KwAEjYqcjNfk',
    url: 'https://www.linkedin.com/in/anesi-igebu/'
  },
  {
    name: 'Itunu R.',
    role: 'iOS Engineer, Verse Communications PBC',
    message: `As a fullstack engineer, Damilola demonstrates a solid grasp of both frontend and backend technologies, consistently delivering clean and efficient code. He has expanded his skills into iOS development, showing a keen ability to adapt to new platforms.

What stands out about Damilola is his eagerness for quickly mastering new technologies. This adaptability, combined with his communication skills, makes him a great teammate. His strong foundation in fullstack development, coupled with his growing expertise in mobile development, positions him as a versatile engineer who can contribute significantly to a wide range of projects, and any organization would be fortunate to have him on their team.`,
    source: 'linkedin',
    avatar: 'https://media.licdn.com/dms/image/v2/D4D03AQGBoGLkLcWjiw/profile-displayphoto-shrink_800_800/profile-displayphoto-shrink_800_800/0/1689161163754?e=1749081600&v=beta&t=6xNYtXsgXTH537xXbxE9Wsb-KbyLYGgwT240sOTedgY',
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
    avatar: 'https://media.licdn.com/dms/image/v2/D4D03AQEMkaP8TyxIjg/profile-displayphoto-shrink_800_800/profile-displayphoto-shrink_800_800/0/1720646770162?e=1749081600&v=beta&t=8HeYYxlDp_QLhQB14BKuXKyJz3z0pPbE31HJdMA3yfk',
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
  align-items: center;
  padding: 1rem 0;
}

.testimonials-section .splide__slide .testimonial-card {
  height: auto;
  opacity: 0.4;
  transform: scale(0.85);
  transition: all 0.4s ease;
  padding: 0.5rem;
}

@media (min-width: 768px) {
  .testimonials-section .splide__slide {
    padding: 1rem;
  }
}

.testimonials-section .splide__slide.is-active .testimonial-card {
  opacity: 1;
  transform: scale(1);
  padding: 0.75rem;
}

.testimonials-section .splide__arrow {
  background: white;
  opacity: 1;
  width: 2.5rem;
  height: 2.5rem;
  border: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.testimonials-section .splide__arrow svg {
  fill: #ef4444;
  width: 1em;
}

.testimonials-section .splide__arrow:hover {
  background: #ef4444;
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
  background: #ef4444;
  transform: scale(1.2);
}

@media (max-width: 640px) {
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