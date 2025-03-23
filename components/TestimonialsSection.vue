<template>
	<section class="my-12 px-4 max-w-5xl mx-auto" data-aos="fade-up">
	  <h2 class="text-2xl md:text-3xl font-bold mb-8 text-center">Testimonials</h2>
  
	  <Swiper
		:slides-per-view="1"
		:space-between="20"
		:breakpoints="{
		  640: { slidesPerView: 1 },
		  768: { slidesPerView: 2 },
		  1024: { slidesPerView: 3 }
		}"
		:autoplay="{ delay: 5000 }"
		:loop="true"
		:pagination="{ clickable: true }"
		:navigation="true"
		class="testimonial-swiper"
	  >
		<SwiperSlide
		  v-for="(testimonial, index) in testimonials"
		  :key="index"
		>
		  <TestimonialCard v-bind="testimonial" />
		</SwiperSlide>
	  </Swiper>
	</section>
  </template>
  
<script setup lang="ts">
  import { ref, onMounted } from 'vue'
  import AOS from 'aos'
  import { Swiper, SwiperSlide } from 'swiper/vue'
  import 'swiper/css'
  import 'swiper/css/pagination'
  import 'swiper/css/navigation'
  
  import TestimonialCard from './TestimonialCard.vue'
  
  onMounted(() => AOS.init({ duration: 800 }))
  
  interface Testimonial {
	name: string
	role: string
	message: string
	avatar?: string
	source?: 'linkedin' | 'twitter' | 'email'
	url?: string
  }
  
  const testimonials = ref<Testimonial[]>([
	{
	  name: 'Jane Doe',
	  role: 'Product Designer',
	  message: 'Working with Damilola was a game changer. His clarity and leadership elevated our entire workflow.',
	  source: 'linkedin',
	  avatar: '/assets/jane.jpg',
	  url: 'https://linkedin.com/in/janedoe'
	},
	{
	  name: 'Michael Eze',
	  role: 'Tech Lead, Versa Nigeria',
	  message: 'One of the sharpest developers I’ve worked with. Brings calm and speed to every sprint.',
	  source: 'email',
	  avatar: '/assets/michael.jpg'
	},
	{
	  name: 'Tina Brooks',
	  role: 'Startup Founder',
	  message: 'Our MVP wouldn’t have shipped without his guidance. Truly grateful.',
	  source: 'twitter',
	  avatar: '/assets/tina.jpg',
	  url: 'https://twitter.com/tinabrooks'
	}
  ])
</script>