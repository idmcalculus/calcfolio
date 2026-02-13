<template>
    <section id="experience" class="my-16 px-6 max-w-(--breakpoint-xl) mx-auto flex flex-col items-center" data-aos="fade-up">
		<div class="w-full text-center md:text-left">
			<h2 class="text-2xl md:text-3xl font-bold">Experience</h2>
			<SectionDivider />
		</div>

		<div
			class="w-full relative pb-10 before:content-[''] before:absolute before:left-5 md:before:left-1/2 /* Mobile axis left, Desktop axis center */
			before:top-0 before:bottom-0 before:w-0.5 before:bg-primary dark:before:bg-primary
			before:-translate-x-1/2 before:z-0"
		>
				<div
					v-for="(job, index) in sortedExperience"
					:key="index"
				class="timeline-item mb-10 relative md:w-[calc(50%-(--spacing(8)))] /* Desktop: Half width minus gap */
					pl-[calc(--spacing(5)+(--spacing(8)))] md:pl-0 /* Mobile: Padding left */
					opacity-0 translate-y-8 animate-fade-in-up"
					:class="[!getSideForJob(index) ? 'md:ml-[calc(50%+(--spacing(8)))]' : 'md:mr-auto']"
					:style="`animation-delay: ${index * 150}ms`"
				>
					<div
						:class="['mb-2 md:mb-0 md:absolute md:top-1/2 md:-translate-y-1/2', 'md:z-10',
						!getSideForJob(index) ? 'md:right-[calc(100%+(--spacing(16)))] md:text-right' : 'md:left-[calc(100%+(--spacing(16)))] md:text-left']"
				>
					<p class="text-sm text-gray-600 dark:text-gray-400 font-semibold whitespace-nowrap">
						{{ job.date }}
					</p>
				</div>

				<div
					class="absolute left-0 top-[calc(50%+(--spacing(3)))] md:top-1/2 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center
					p-1 rounded-full shadow-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-bg
					transition-all duration-300 hover:scale-110 hover:shadow-lg hover:border-primary"
					:class="[!getSideForJob(index) ? 'md:left-[calc(-1.1*(--spacing(12)))]' : 'md:left-auto md:right-[calc(-1.1*(--spacing(12)))]']"
				>
					<Icon name="lucide:briefcase" class="w-5 h-5 text-primary transition-transform duration-300 hover:rotate-12" />
				</div>

					<div
	                    class="w-full md:max-w-lg relative bg-white dark:bg-zinc-900 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-700
	                        before:content-[''] before:absolute before:top-1/2 before:-translate-y-1/2 before:h-0.5 before:bg-primary dark:before:bg-primary before:z-0
	                        before:left-[calc(-1*(--spacing(4)))] before:w-4 /* Mobile default: connector starts from left edge */
							transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-primary/50"
	                    :class="[getSideForJob(index) ? 'md:ml-auto md:before:left-auto md:before:right-[calc(-1*(--spacing(4)))]' : '']"
	                >
	                    <h3 class="text-lg md:text-xl font-bold text-gray-800 dark:text-gray-200 break-words">
							{{ job.company }} · {{ job.title }}
						</h3>
	                    <ul class="mt-3 space-y-2 text-sm md:text-base text-gray-700 dark:text-gray-300 leading-relaxed">
							<li
								v-for="(highlight, highlightIndex) in job.highlights"
								:key="`${index}-${highlightIndex}`"
								class="flex items-start gap-2"
							>
								<span class="mt-2 h-1.5 w-1.5 rounded-full bg-primary shrink-0" />
								<span>{{ highlight }}</span>
							</li>
						</ul>
	                </div>
				</div>
			<div class="absolute -bottom-4 left-5 md:left-1/2! -translate-x-1/2 text-primary dark:text-primary z-10 animate-bounce">
				<Icon name="lucide:chevron-down" class="w-6 h-6" />
			</div>
		</div>
	</section>
</template>

<script setup lang="ts">
    import { ref, computed } from 'vue'

	    interface Job {
	        title: string
	        company: string
	        date: string
	        highlights: string[]
	    }

    const experience = ref<Job[]>([
			{
				title: 'Senior Software Engineer',
				company: 'Trusted Transfers',
				date: 'October 2024 – Present',
				highlights: [
					'Automated AWS infrastructure provisioning with Pulumi, reducing setup time by 60% and standardising cloud environments.',
					'Improved API and database performance, reducing transaction latency by up to 95% through query optimisation and compute offloading.',
					'Enhanced React frontend and mobile service integrations, improving page speed by 45% and platform responsiveness under load.'
				]
			},
			{
				title: 'Software Engineer II',
				company: 'Aeeiee Inc.',
				date: 'October 2022 – September 2024',
				highlights: [
					'Engineered SwiftUI iOS features and resolved iCloud synchronisation failures, reducing customer complaints by 75%.',
					'Designed OAuth2 and MFA secured backend systems, strengthening authentication and access control across platforms.',
					'Automated CI and CD pipelines, achieving 99.9% deployment success and accelerating release frequency.'
				]
			},
			{
				title: 'Full Stack Engineer',
				company: 'Aeeiee Inc.',
				date: 'September 2020 – September 2022',
				highlights: [
					'Optimised Laravel APIs and MySQL data models, reducing response latency by 50%.',
					'Migrated and remodelled over 100k production records, reducing storage footprint by 83%.',
					'Built analytics dashboards and CRM integrations, increasing lead conversion rates by 50%.'
				]
			},
			{
				title: 'Senior Fullstack Developer',
				company: 'Versa Haven Resources Limited',
				date: 'February 2020 – July 2020',
				highlights: [
					'Led a team of three developers to rebuild a multi investment platform frontend and reporting dashboards.',
					'Integrated payment gateways and engineered financial reporting features to support transaction processing and reconciliation.',
					'Improved API performance and strengthened platform security in alignment with OWASP best practices.'
				]
			},
	        {
				title: 'Full Stack Engineer',
				company: 'RCCG ICT Unit',
				date: 'March 2018 – February 2020',
				highlights: [
					'Designed Java Spring microservices, improving transaction throughput by 40%.',
					'Built React and Next.js administrative platforms, improving operational visibility and reducing manual processing.',
					'Implemented OAuth2, JWT, and role based access control across distributed systems.'
				]
			},
			{
				title: 'Full Stack Engineer',
				company: 'HNG Tech',
				date: 'June 2017 – February 2018',
				highlights: [
					'Built LMS platforms using Oracle JET and Laravel, increasing learner engagement by 25%.',
					'Engineered real time notification systems, reducing delivery latency by 40%.',
					'Developed serverless Slack automation tools, reducing manual HR coordination effort.'
				]
			},
	    ])

    // Group experiences so roles from the same company sit directly under each other
    const sortedExperience = computed<Job[]>(() => {
        const groups = new Map<string, Job[]>()
        const companyOrder: string[] = []

        for (const job of experience.value) {
            if (!groups.has(job.company)) {
                groups.set(job.company, [])
                companyOrder.push(job.company)
            }
            groups.get(job.company)!.push(job)
        }

        const flattened: Job[] = []
        for (const company of companyOrder) {
            const items = groups.get(company)
            if (items) flattened.push(...items)
        }
        return flattened
    })

    // Group jobs by company and assign sides
    const companySideMap = computed(() => {
        const map = new Map<string, boolean>()
        let currentSide = true // true = left, false = right
        let lastCompany = ''

        sortedExperience.value.forEach((job) => {
            if (!map.has(job.company)) {
                if (lastCompany && lastCompany !== job.company) {
                    currentSide = !currentSide
                }
                map.set(job.company, currentSide)
                lastCompany = job.company
            }
        })

        return map
    })

    // Get the side for a specific job based on its company
    const getSideForJob = (index: number): boolean => {
        const job = sortedExperience.value[index]
        if (!job) return true
        return companySideMap.value.get(job.company) ?? true
    }

    // Check if the job above has the same company
    // const hasSameCompanyAbove = (index: number): boolean => {
    //     if (index === 0) return false
    //     const currentJob = sortedExperience.value[index]
    //     const previousJob = sortedExperience.value[index - 1]
    //     if (!currentJob || !previousJob) return false
    //     return currentJob.company === previousJob.company
    // }

</script>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(2rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes grow-vertical {
    from {
        height: 0;
        opacity: 0;
    }
    to {
        height: 40px;
        opacity: 1;
    }
}

@keyframes pulse-soft {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
}

.animate-grow-vertical {
    animation: grow-vertical 0.4s ease-out forwards;
}

.animate-pulse-soft {
    animation: pulse-soft 2s ease-in-out infinite;
}

.timeline-item {
    will-change: transform, opacity;
}
</style>
