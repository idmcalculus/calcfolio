<template>
	<section class="my-12 px-6 max-w-(--breakpoint-xl) mx-auto" :data-aos="$nuxt.isHydrating ? undefined : 'fade-up'">
		<div class=" mb-8 text-center md:text-left">
			<h2 class="text-2xl md:text-3xl font-bold">Some of my work</h2>
			<SectionDivider />
		</div>
  
		<div class="grid grid-cols-1 sm:grid-cols-[repeat(auto-fill,minmax(400px,1fr))] gap-6">
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
	description: "A full-stack e-commerce platform made with love for my wife's premium bakery - Jumis Cake Studio. Features online ordering, product customization, secure payments via Stripe and PayPal, and a modern user interface built with React and Shadcn UI.",
	imageUrl: "https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?q=80&w=800&auto=format",
	tags: ["React", "TypeScript", "Vite", "HTML5", "CSS3", "Tailwind CSS", "Shadcn UI", "Radix UI", "React Router", "TanStack Query", "React Hook Form", "Zod", "Prisma", "PostgreSQL", "Stripe", "PayPal", "Node.js", "Bun", "ESLint", "Git"],
	github: "https://github.com/idmcalculus/jumis-cake-studio"
	},
	{
		title: "Word Game Challenge",
		description: "An open-source word-guessing game using vanilla JavaScript and a modular architecture, ensuring responsiveness. To enhance the user experience, I implemented real-time letter validation and an interactive colour-coded feedback system. I integrated the Datamuse API with custom filtering algorithms to create an adaptive word dictionary with appropriate difficulty levels for words of varying lengths. Additionally, I built a performance-optimized game state management system with local storage integration, which included real-time scoring, game statistics tracking, and persistent high-score functionality.",
		imageUrl: "https://res.cloudinary.com/idmcalculus/image/upload/w_1000,ar_16:9,c_fill,g_auto,e_sharpen/v1756435214/wordgamechallengess.png",
		tags: ["HTML", "CSS", "JavaScript", "APIs", "state management", "modular design", "responsive design", "interactive feedback"],
		link: "https://word-game-challenge.vercel.app/",
		github: "https://github.com/idmcalculus/wordGameChallenge"
	},
	{
		title: "AEEIEE",
		description: "A professional, modern and responsive website for my company, Aeeiee, showcasing our products, services and more. I led the development of the custom WordPress theme and implemented various interactive features using React and PHP.",
		imageUrl: "/assets/images/aeeiee.png",
		tags: ["WordPress", "Node.js", "jQuery", "PHP", "HTML5", "CSS3", "SCSS", "Bootstrap", "MySQL", "Gutenberg", "REST API", "Docker", "AWS (S3, CloudFront)", "CI/CD", "Git"],
		link: "https://aeeiee.com"
	},
	{
		title: "Catch The Ghost App",
		description: "A productivity app built with React Native and Firebase. I designed the user interface and implemented the real-time data synchronization feature using Firebase.",
		imageUrl: "/assets/images/ctg.png",
		tags: ["Swift", "SwiftUI", "UIKit", "CoreData", "Firebase", "Xcode", "iOS", "Swift Package Manager", "Combine", "AVFoundation", "StoreKit", "App Store Connect", "Git", "TestFlight"],
		link: "https://www.aeeiee.com/catch-the-ghost/"
	},
	{
		title: "Precis",
		description: "Precis is a full-stack web application for visualizing and analyzing rainfall time-series data. The platform provides interactive data visualization tools, statistical analysis, and geospatial mapping capabilities to help users understand rainfall patterns in central Birmingham.",
		imageUrl: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=800&auto=format",
		tags: ["Python", "Flask", "JavaScript", "HTML5", "CSS3", "SCSS", "Pandas", "NumPy", "Plotly", "Plotly.js", "Leaflet.js", "noUiSlider", "Flatpickr", "Webpack", "Babel", "Axios", "Bootstrap", "SQLite", "PostgreSQL", "Docker", "AWS (ECS, RDS, ECR, VPC, ALB)", "IaC (Pulumi)", "GitHub Actions", "CI/CD", "Git"],
		github: "https://github.com/idmcalculus/precis"
	},
	{
		title: "Task Management App",
		description: "A full-stack task management application using React 19 and Node.js. I implemented real-time task tracking, file attachments, and email notifications while ensuring secure user authentication and authorisation. The RESTful API was engineered with Express.js and MongoDB and features comprehensive Swagger documentation, rate limiting, and secure session management. I implemented advanced frontend features using the modern React ecosystem, and the application was containerised using Docker.",
		imageUrl: "https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=800&auto=format",
		tags: ["JavaScript", "Node.js", "HTML5", "CSS3", "SCSS", "React 19", "React Query", "React Router DOM", "Formik", "Yup", "React Select", "React Slick", "Axios", "Jest", "React Testing Library", "Express.js", "MongoDB", "Mongoose", "JWT", "Morgan", "Multer", "Swagger UI", "Cors", "Docker", "Git", "npm", "ESLint", "Babel", "AWS S3", "MongoDB Atlas", "Fly.io", "GitHub Actions", "CI/CD"],
		link: "https://task-manager-api.fly.dev/",
		github: "https://github.com/idmcalculus/tasktivate"
	},
	{
		title: "Dress Measurement App",
		description: "Mobile-first e-commerce application for a dress boutique",
		imageUrl: "/assets/images/dm.png",
		tags: ["Swift", "UIKit", "SQLite", "Xcode", "iOS", "CocoaPods", "CloudKit", "App Store Connect", "Git", "TestFlight"],
		link: "https://www.aeeiee.com/dress-measurement/"
	},
	{
		title: "Iridium Go Exec Product Page",
		description: "A professional, modern and responsive website for Aeeiee Inc, showcasing their products, services and more. I led the development of the custom WordPress theme and implemented various interactive features using React and PHP.",
		imageUrl: "https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?q=80&w=800&auto=format",
		tags: ["Vue.js", "Node.js", "MongoDB"],
		link: "https://www.iridium.com/go-exec/"
	}
  ])
</script>