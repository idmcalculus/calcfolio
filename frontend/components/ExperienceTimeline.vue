<template>
    <section class="my-16 px-6 max-w-(--breakpoint-xl) mx-auto flex flex-col items-center" data-aos="fade-up">
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
				<!-- Company badge for first role in company group -->
				<div
					v-if="isFirstInCompanyGroup(index)"
					class="hidden md:block absolute -top-10 px-3 py-1 rounded-full bg-gradient-to-r from-primary/20 to-primary/10
						border border-primary/30 backdrop-blur-sm animate-pulse-soft z-20"
					:class="[!getSideForJob(index) ? 'left-0' : 'right-0']"
				>
					<p class="text-xs font-bold text-primary uppercase tracking-wider">{{ job.company }}</p>
				</div>

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
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300 break-words uppercase">{{ job.title }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ job.type || 'Full-time' }}</p>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 break-words">{{ job.description }}</p>
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
        description: string
        type?: 'full-time' | 'part-time' | 'contract' | 'temporary'
    }

    const experience = ref<Job[]>([
		{
			title: 'Senior Software & Data Engineer',
			company: 'Trusted Transfers',
			date: 'January 2023 – June 2025',
			description: 'I built Power BI/Tableau dashboards that improved decision-making by 40%. I optimised APIs, PostgreSQL, and Spark pipelines—cutting latency by up to 95%. I deployed secure AWS microservices with Pulumi and halved provisioning and deploy times.',
			type: 'part-time'
		},
		{
			title: 'Software Engineer II',
			company: 'Aeeiee Inc.',
			date: 'October 2022 – September 2024',
			description: 'I developed iOS apps with SwiftUI, fixing iCloud sync to reduce complaints by 75%. I secured backends with OAuth2, MFA, and serverless OTP flows. I automated CI/CD for 99.9% success, launched a productivity app (~10k users), and modernised admin panels.',
			type: 'full-time'
		},
		{
			title: 'Senior Full Stack Engineer',
			company: 'RCCG ICT Unit',
			date: 'December 2020 – October 2022',
			description: 'I migrated church apps into Node.js microservices with PostgreSQL and S3 uploads. I introduced TDD, automated CI/CD, and reduced release cycles from weeks to days. I built financial and records services that improved usability by 35% and productivity by 40%.',
			type: 'part-time'
		},
		{
			title: 'Full Stack Engineer',
			company: 'Aeeiee Inc.',
			date: 'September 2020 – September 2022',
			description: 'I optimised APIs and databases, halving latency and reducing storage by 83%. I automated RDS backups with Lambda and built Vue/Tailwind/D3 dashboards. I also integrated Pipedrive with Gravity Forms, boosting conversion rates by 50%.',
			type: 'full-time'
		},
		{
			title: 'Senior Fullstack Developer',
			company: 'Versa Nigeria',
			date: 'February 2020 – July 2020',
			description: 'I led a team of three to rebuild dashboards for a multi-investment platform. I integrated payment gateways and financial reporting while cutting downtime. I also reduced errors, improved REST API speed, and strengthened OWASP-based security.',
			type: 'full-time'
		},
		{
			title: 'Fullstack Engineer',
			company: 'HNG Tech',
			date: 'March 2017 – February 2020',
			description: 'I built an LMS frontend and blog that boosted engagement by 25%. I implemented real-time notifications and optimised data flows, cutting latency by 40%. I also built a Slack bot (AWS Lambda/DynamoDB) that automated HR tasks and scaled slash commands.',
			type: 'part-time'
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

    // Check if this is the first role in a company group
    const isFirstInCompanyGroup = (index: number): boolean => {
        if (index === 0) return true
        const currentJob = sortedExperience.value[index]
        const previousJob = sortedExperience.value[index - 1]
        if (!currentJob || !previousJob) return true
        return currentJob.company !== previousJob.company
    }
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