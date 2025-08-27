<template>
    <section class="my-16 px-6 max-w-(--breakpoint-xl) mx-auto flex flex-col items-center" data-aos="fade-up">
		<div class="w-full text-center md:text-left">
			<h2 class="text-2xl md:text-3xl font-bold">Experience</h2>
			<SectionDivider />
		</div>

		<div
			class="w-full relative pb-10 before:content-[''] before:absolute before:left-5 md:before:left-1/2 /* Mobile axis left, Desktop axis center */
			before:top-0 before:bottom-0 before:w-0.5 before:bg-primary dark:before:bg-primary
			before:-translate-x-1/2 before:z-0
		">
			<div
				v-for="(job, index) in experience"
				:key="index"
				class="mb-10 relative md:w-[calc(50%-(--spacing(8)))] /* Desktop: Half width minus gap */
					pl-[calc(--spacing(5)+(--spacing(8)))] md:pl-0 /* Mobile: Padding left */"
				:class="[!isEven(index) ? 'md:ml-[calc(50%+(--spacing(8)))]' : 'md:mr-auto']"
			>
				<div
					:class="['mb-2 md:mb-0 md:absolute md:top-1/2 md:-translate-y-1/2', 'md:z-10',
					!isEven(index) ? 'md:right-[calc(100%+(--spacing(16)))] md:text-right' : 'md:left-[calc(100%+(--spacing(16)))] md:text-left']"
				>
					<p class="text-sm text-gray-600 dark:text-gray-400 font-semibold whitespace-nowrap">
						{{ job.date }}
					</p>
				</div>

				<div
					class="absolute left-0 top-[calc(50%+(--spacing(3)))] md:top-1/2 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center
					p-1 rounded-full shadow-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-bg"
					:class="[!isEven(index) ? 'md:left-[calc(-1.1*(--spacing(12)))]' : 'md:left-auto md:right-[calc(-1.1*(--spacing(12)))]']"
				>
					<Icon name="lucide:briefcase" class="w-5 h-5 text-primary" />
				</div>

				<div
                    class="w-full md:max-w-lg relative bg-white dark:bg-zinc-900 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-700
                        before:content-[''] before:absolute before:top-1/2 before:-translate-y-1/2 before:h-0.5 before:bg-primary dark:before:bg-primary before:z-0
                        before:left-[calc(-1*(--spacing(4)))] before:w-4 /* Mobile default: connector starts from left edge */"
                    :class="[isEven(index) ? 'md:ml-auto md:before:left-auto md:before:right-[calc(-1*(--spacing(4)))]' : '']"
                >
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300 break-words uppercase">{{ job.title }}</h3>
                    <p class="text-sm text-primary break-words mt-2">{{ job.company }}</p>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 break-words">{{ job.description }}</p>
                </div>
			</div>
			<div class="absolute -bottom-4 left-5 md:left-1/2! -translate-x-1/2 text-primary dark:text-primary z-10">
				<Icon name="lucide:chevron-down" class="w-6 h-6" />
			</div>
		</div>
	</section>
</template>

<script setup lang="ts">
    import { ref } from 'vue'

    interface Job {
        title: string
        company: string
        date: string
        description: string
    }

    const experience = ref<Job[]>([
		{
			title: 'Software Engineer II',
			company: 'Aeeiee Inc.',
			date: 'October 2022 – September 2024',
			description: 'Lorem ipsum dolor sit amet consectetur. Posuere aliquam nisi tellus pellentesque. Nullam adipiscing id cras velit sit eget pharetra urna ornare.'
		},
		{
			title: 'Full Stack Engineer',
			company: 'Aeeiee Inc.',
			date: 'September 2020 – September 2022',
			description: 'Lorem ipsum dolor sit amet consectetur. Posuere aliquam nisi tellus pellentesque. Nullam adipiscing id cras velit sit eget pharetra urna ornare.'
		},
		{
			title: 'Senior Fullstack Developer',
			company: 'Versa Nigeria',
			date: 'February 2020 – July 2020',
			description: 'Praesent efficitur eget lacus sed fermentum. Nullam pretium ex nec erat luctus.'
		},
		{
			title: 'Fullstack Engineer',
			company: 'HNG Tech',
			date: 'March 2019 – February 2020',
			description: 'Pharetra placerat quis neque turpis dignissim. Vulputate enim suspendisse ut massa ac sed.'
		},
    ])

    const isEven = (index: number) => index % 2 === 0
</script>