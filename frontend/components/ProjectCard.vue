<template>
	<article
		class="project-card group focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2"
		:aria-label="`${title} project`"
	>
		<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
			<NuxtImg
				:src="imageUrl"
				:alt="`Screenshot or preview of ${title} project`"
				class="w-full h-56 object-cover transition-transform duration-300 group-hover:scale-[1.03]"
				loading="lazy"
			/>
		</div>

		<div class="pt-4">
			<div class="flex items-center justify-between gap-2 mb-2">
				<h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
				<div class="flex gap-2 text-sm shrink-0">
					<a
						v-if="github"
						:href="github"
						target="_blank"
						rel="noopener noreferrer"
						:aria-label="`View ${title} source code on GitHub`"
						class="icon-link"
					>
						<Icon name="simple-icons:github" size="16" />
					</a>
					<a
						v-if="link"
						:href="link"
						target="_blank"
						rel="noopener noreferrer"
						:aria-label="`Visit live demo of ${title}`"
						class="icon-link"
					>
						<Icon name="lucide:external-link" size="16" />
					</a>
				</div>
			</div>

			<p class="project-description text-sm text-gray-700 dark:text-gray-300 mb-4">
				{{ description }}
			</p>

			<div class="flex flex-wrap gap-2" role="list" aria-label="Technologies used">
				<span
					v-for="(tag, i) in visibleTags"
					:key="`${tag}-${i}`"
					role="listitem"
					class="tag-pill"
					:aria-label="`Technology: ${tag}`"
				>
					{{ tag }}
				</span>
				<span v-if="hiddenTagsCount > 0" class="tag-pill tag-pill-muted">+{{ hiddenTagsCount }} more</span>
			</div>
		</div>
	</article>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
	title: string
	description: string
	tags: string[]
	imageUrl: string
	link?: string
	github?: string
}>()

const MAX_VISIBLE_TAGS = 6

const visibleTags = computed(() => props.tags.slice(0, MAX_VISIBLE_TAGS))
const hiddenTagsCount = computed(() => Math.max(props.tags.length - MAX_VISIBLE_TAGS, 0))
</script>

<style scoped>
.project-card {
	background: rgb(255 255 255 / 0.7);
	border: 1px solid rgb(229 231 235);
	border-radius: 0.75rem;
	padding: 1rem;
	transition: all 0.3s ease;
}

.project-card:hover {
	transform: translateY(-3px);
	box-shadow: 0 18px 28px rgb(0 0 0 / 0.15);
}

.dark .project-card {
	background: rgb(24 24 27 / 0.85);
	border-color: rgb(63 63 70);
}

.project-description {
	display: -webkit-box;
	-webkit-line-clamp: 4;
	-webkit-box-orient: vertical;
	overflow: hidden;
}

.icon-link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0.35rem;
	border-radius: 0.375rem;
	color: rgb(107 114 128);
	transition: all 0.2s ease;
}

.icon-link:hover {
	color: var(--color-primary);
	background: rgb(0 0 0 / 0.04);
}

.icon-link:focus-visible {
	outline: none;
	color: var(--color-primary);
	box-shadow: 0 0 0 3px rgb(245 71 71 / 0.22);
}

.dark .icon-link {
	color: rgb(163 163 163);
}

.dark .icon-link:hover {
	background: rgb(255 255 255 / 0.08);
}

.tag-pill {
	display: inline-flex;
	align-items: center;
	border: 1px solid rgb(252 165 165 / 0.45);
	color: rgb(185 28 28);
	background: rgb(254 242 242 / 0.8);
	border-radius: 9999px;
	padding: 0.2rem 0.55rem;
	font-size: 0.7rem;
	font-weight: 600;
}

.tag-pill-muted {
	border-color: rgb(209 213 219);
	color: rgb(75 85 99);
	background: rgb(249 250 251);
}

.dark .tag-pill {
	border-color: rgb(185 28 28 / 0.5);
	color: rgb(252 165 165);
	background: rgb(69 10 10 / 0.55);
}

.dark .tag-pill-muted {
	border-color: rgb(82 82 91);
	color: rgb(212 212 216);
	background: rgb(39 39 42);
}
</style>
