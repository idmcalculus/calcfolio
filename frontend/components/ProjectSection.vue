<template>
		<section class="my-12 px-6 max-w-(--breakpoint-xl) mx-auto" :data-aos="$nuxt.isHydrating ? undefined : 'fade-up'">
			<div class="mb-8 text-center md:text-left">
				<h2 class="text-2xl md:text-3xl font-bold">Selected Work</h2>
				<SectionDivider />
				<p class="max-w-3xl text-sm md:text-base text-gray-600 dark:text-gray-400">
					A cross-section of platforms I&apos;ve built, spanning product interfaces, backend systems, and cloud delivery.
				</p>
			</div>
	  
			<div class="grid grid-cols-1 sm:grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-7">
				<ProjectCard
					v-for="(project, i) in limitedProjects"
					:key="i"
				:title="project.title"
				:description="project.description"
				:tags="project.tags"
				:image-url="project.imageUrl"
				:github="project.github"
				:link="project.link"
			/>
		</div>
	</section>
</template>
  
<script setup lang="ts">
  import { ref, computed } from 'vue'
  import ProjectCard from './ProjectCard.vue'
  
  interface Props {
	limit?: number
  }

  const props = withDefaults(defineProps<Props>(), {
	limit: undefined
  })

  const limitedProjects = computed(() => props.limit ? projects.value.slice(0, props.limit) : projects.value)

  interface Project {
	title: string
	description: string
	tags: string[]
	imageUrl: string
	link?: string
	github?: string
  }
  
	const projects = ref<Project[]>([
	{
	title: "Jumis Cake Studio",
	description: "Full-stack bakery commerce platform with customizable orders, Stripe and PayPal checkout, and a polished React storefront.",
	imageUrl: "https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?q=80&w=800&auto=format",
	tags: ["React", "TypeScript", "Vite", "HTML5", "CSS3", "Tailwind CSS", "Shadcn UI", "Radix UI", "React Router", "TanStack Query", "React Hook Form", "Zod", "Prisma", "PostgreSQL", "Stripe", "PayPal", "Node.js", "Bun", "ESLint", "Git"],
	github: "https://github.com/idmcalculus/jumis-cake-studio"
	},
	{
		title: "Word Game Challenge",
		description: "Open-source word game with real-time validation, adaptive Datamuse dictionary logic, and persistent high-score tracking.",
		imageUrl: "https://res.cloudinary.com/idmcalculus/image/upload/w_1000,ar_16:9,c_fill,g_auto,e_sharpen/v1756435214/wordgamechallengess.png",
		tags: ["HTML", "CSS", "JavaScript", "APIs", "state management", "modular design", "responsive design", "interactive feedback"],
		link: "https://word-game-challenge.vercel.app/",
		github: "https://github.com/idmcalculus/wordGameChallenge"
	},
	{
		title: "AEEIEE",
		description: "Corporate website for Aeeiee built on a custom WordPress theme with React-driven interactions and scalable content modules.",
		imageUrl: "/assets/images/aeeiee.png",
		tags: ["WordPress", "Node.js", "jQuery", "PHP", "HTML5", "CSS3", "SCSS", "Bootstrap", "MySQL", "Gutenberg", "REST API", "Docker", "AWS (S3, CloudFront)", "CI/CD", "Git"],
		link: "https://aeeiee.com"
	},
	{
		title: "Catch The Ghost App",
		description: "Gamified Pomodoro iOS app where I shipped auth and profile flows plus modular features supporting more than 10k users.",
		imageUrl: "/assets/images/ctg.png",
		tags: ["Swift", "SwiftUI", "UIKit", "CoreData", "Firebase", "Xcode", "iOS", "Swift Package Manager", "Combine", "AVFoundation", "StoreKit", "App Store Connect", "Git", "TestFlight"],
		link: "https://www.aeeiee.com/catch-the-ghost/"
	},
	{
		title: "Precis",
		description: "Rainfall analytics web app for time-series and geospatial exploration with interactive dashboards and statistical tooling.",
		imageUrl: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=800&auto=format",
		tags: ["Python", "Flask", "JavaScript", "HTML5", "CSS3", "SCSS", "Pandas", "NumPy", "Plotly", "Plotly.js", "Leaflet.js", "noUiSlider", "Flatpickr", "Webpack", "Babel", "Axios", "Bootstrap", "SQLite", "PostgreSQL", "Docker", "AWS (ECS, RDS, ECR, VPC, ALB)", "IaC (Pulumi)", "GitHub Actions", "CI/CD", "Git"],
		github: "https://github.com/idmcalculus/precis"
	},
	{
		title: "Task Management App",
		description: "Full-stack task platform with secure auth, real-time workflows, file uploads, and documented Express plus MongoDB APIs.",
		imageUrl: "https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=800&auto=format",
		tags: ["JavaScript", "Node.js", "HTML5", "CSS3", "SCSS", "React 19", "React Query", "React Router DOM", "Formik", "Yup", "React Select", "React Slick", "Axios", "Jest", "React Testing Library", "Express.js", "MongoDB", "Mongoose", "JWT", "Morgan", "Multer", "Swagger UI", "Cors", "Docker", "Git", "npm", "ESLint", "Babel", "AWS S3", "MongoDB Atlas", "Fly.io", "GitHub Actions", "CI/CD"],
		link: "https://task-manager-api.fly.dev/",
		github: "https://github.com/idmcalculus/tasktivate"
	},
	{
		title: "Dress Measurement App",
		description: "iOS measurement app with cloud sync and exports, including CloudKit fixes that reduced sync-related complaints by 75%.",
		imageUrl: "/assets/images/dm.png",
		tags: ["Swift", "UIKit", "SQLite", "Xcode", "iOS", "CocoaPods", "CloudKit", "App Store Connect", "Git", "TestFlight"],
		link: "https://www.aeeiee.com/dress-measurement/"
	},
	{
		title: "Iridium Go Exec Product Page",
		description: "Flexible WordPress page-builder implementation for satellite product marketing, including the Iridium GO exec launch page.",
		imageUrl: "/assets/images/goexec.png",
		tags: ["PHP", "HTML5", "CSS3", "SCSS", "Bootstrap", "WordPress", "ACF", "jQuery"],
		link: "https://www.iridium.com/go-exec/"
	}
  ])
</script>
