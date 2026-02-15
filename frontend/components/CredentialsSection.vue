<template>
  <section
    class="my-12 px-6 max-w-(--breakpoint-xl) mx-auto"
    :data-aos="$nuxt.isHydrating ? undefined : 'fade-up'"
  >
    <div class="mb-6 w-full text-center md:text-left">
      <h2 class="text-2xl md:text-3xl font-extrabold">{{ title }}</h2>
      <SectionDivider />
      <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 max-w-3xl">
        {{ description }}
      </p>
    </div>

    <div
      v-if="showRefineControls && featuredScopedCredentials.length > 0"
      class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto] md:items-end"
    >
      <label class="block">
        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
          Search
        </span>
        <input
          v-model="searchQuery"
          type="search"
          inputmode="search"
          autocomplete="off"
          placeholder="Title or provider"
          aria-label="Search credentials"
          class="w-full rounded-md border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/35 focus:border-primary/40 transition-colors"
        >
      </label>

      <label class="block">
        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
          Provider
        </span>
        <select
          v-model="selectedProvider"
          aria-label="Filter credentials by provider"
          class="w-full rounded-md border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/35 focus:border-primary/40 transition-colors"
        >
          <option value="all">All providers</option>
          <option
            v-for="provider in providerOptions"
            :key="provider"
            :value="provider"
          >
            {{ provider }}
          </option>
        </select>
      </label>

      <div class="flex items-center justify-between gap-3 md:justify-end">
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Showing {{ displayedCredentials.length }} of {{ filteredCredentials.length }}
        </p>
        <button
          v-if="hasActiveRefine"
          type="button"
          class="rounded-md border border-gray-300 dark:border-zinc-700 px-2.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:border-primary/45 hover:text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/35"
          aria-label="Clear credential filters"
          @click="clearRefine"
        >
          Clear
        </button>
      </div>
    </div>

    <div
      v-if="displayedCredentials.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 items-stretch"
    >
      <article
        v-for="credential in displayedCredentials"
        :key="credential.id"
        :class="['credential-card', { 'credential-card-large': largeCards }]"
      >
        <div class="flex items-stretch gap-4 h-full">
          <button
            type="button"
            :class="['credential-thumb-button', { 'credential-thumb-button-large': largeCards }]"
            :disabled="!credential.imageUrl"
            :aria-label="`Preview ${credential.title} certificate`"
            @click="openPreview(credential)"
          >
            <img
              v-if="thumbnailUrlFor(credential) && !brokenThumbnailIds[credential.id]"
              :src="thumbnailUrlFor(credential) || undefined"
              :alt="`${credential.title} certificate preview`"
              class="credential-thumb-image"
              loading="lazy"
              @error="markThumbnailBroken(credential.id)"
            >
            <div
              v-else
              class="credential-thumb-fallback"
              :aria-label="`${credential.provider} credential`"
            >
              <Icon :name="resolveProviderIcon(credential)" class="w-4 h-4" />
            </div>
            <span
              v-if="credential.imageUrl"
              class="credential-thumb-overlay"
            >
              Preview
            </span>
          </button>

          <div class="min-w-0 flex-1 flex flex-col">
            <h3 :class="['credential-title font-semibold text-gray-900 dark:text-gray-100 leading-snug', largeCards ? 'text-base' : 'text-sm']">
              {{ credential.title }}
            </h3>
            <p :class="['mt-1 text-gray-600 dark:text-gray-400', largeCards ? 'text-sm' : 'text-xs']">
              {{ credential.provider }} · {{ credential.issuedAt }}
            </p>

            <a
              :href="credential.verificationUrl"
              target="_blank"
              rel="noopener noreferrer"
              :aria-label="`Verify ${credential.title}`"
              class="mt-auto pt-2 credential-verify-link"
            >
              Verify
              <Icon name="lucide:external-link" class="w-3.5 h-3.5" />
            </a>
          </div>
        </div>
      </article>
    </div>

    <div
      v-else-if="filteredCredentials.length === 0"
      class="rounded-lg border border-dashed border-gray-300 dark:border-zinc-700 p-5 text-sm text-gray-600 dark:text-gray-400"
    >
      {{ resolvedEmptyMessage }}
    </div>

    <div v-if="canLoadMore" class="mt-6 text-center">
      <button
        type="button"
        class="inline-flex items-center justify-center rounded-md px-5 py-2.5 border border-primary/40 text-primary font-semibold hover:bg-primary hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        aria-label="Load more credentials"
        @click="loadMoreCredentials"
      >
        Load More Credentials
      </button>
    </div>

    <div v-if="showViewAllLink" class="mt-5 text-center md:text-left">
      <NuxtLink
        to="/credentials"
        class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-red-700 transition-colors"
        aria-label="Show all credentials"
      >
        Show all credentials
        <Icon name="lucide:arrow-right" class="w-4 h-4" />
      </NuxtLink>
    </div>

    <TransitionRoot appear :show="Boolean(selectedCredential)" as="template">
      <Dialog class="relative z-50" as="div" @close="closePreview">
        <TransitionChild
          as="template"
          enter="duration-200 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-150 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/55" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-3xl rounded-xl bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 shadow-2xl p-4 md:p-6 relative">
                <button
                  type="button"
                  class="absolute top-3 right-3 rounded-md p-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors"
                  aria-label="Close credential preview"
                  @click="closePreview"
                >
                  <Icon name="lucide:x" class="w-5 h-5" />
                </button>

                <div v-if="selectedCredential" class="space-y-4">
                  <div class="pr-12">
                    <h3 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100">
                      {{ selectedCredential.title }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                      {{ selectedCredential.provider }} · {{ selectedCredential.issuedAt }}
                    </p>
                  </div>

                  <div class="credential-preview-surface">
                    <img
                      v-if="previewUrl && !previewImageLoadFailed && !shouldUseIframePreview"
                      :src="previewUrl"
                      :alt="`${selectedCredential.title} certificate`"
                      class="credential-preview-image"
                      @error="previewImageLoadFailed = true"
                    >

                    <iframe
                      v-else-if="previewUrl && shouldUseIframePreview"
                      :src="previewUrl"
                      class="credential-preview-document"
                      loading="lazy"
                      title="Credential document preview"
                    />

                    <div
                      v-else
                      class="h-full w-full flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 gap-2"
                    >
                      <Icon name="lucide:image-off" class="w-6 h-6" />
                      <p class="text-sm">Preview unavailable for this credential.</p>
                    </div>
                  </div>

                  <a
                    :href="selectedCredential.verificationUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-red-700 transition-colors"
                    :aria-label="`Verify ${selectedCredential.title}`"
                  >
                    Verify credential
                    <Icon name="lucide:external-link" class="w-4 h-4" />
                  </a>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </section>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { computed, onUnmounted, ref, watch } from 'vue'
import SectionDivider from './SectionDivider.vue'
import { credentials, type Credential } from '~/data/credentials'

interface Props {
  limit?: number
  showViewAll?: boolean
  featuredOnly?: boolean
  showRefineControls?: boolean
  syncFiltersToQuery?: boolean
  querySearchKey?: string
  queryProviderKey?: string
  enableLoadMore?: boolean
  initialLoadCount?: number
  loadMoreStep?: number
  largeCards?: boolean
  title?: string
  description?: string
  emptyMessage?: string
}

const props = withDefaults(defineProps<Props>(), {
  limit: 6,
  showViewAll: true,
  featuredOnly: true,
  showRefineControls: false,
  syncFiltersToQuery: false,
  querySearchKey: 'q',
  queryProviderKey: 'provider',
  enableLoadMore: false,
  initialLoadCount: 15,
  loadMoreStep: 12,
  largeCards: false,
  title: 'Credentials',
  description: 'Verified learning and certifications from platforms such as Codecademy, Udemy, and other professional programs.',
  emptyMessage: 'Credentials will appear here soon.'
})

const parseIssuedAtTimestamp = (issuedAt: string): number => {
  const parsed = Date.parse(issuedAt)
  if (!Number.isNaN(parsed)) return parsed

  // Fall back to a year-only parse if the date string format is incomplete.
  const yearMatch = issuedAt.match(/\b(19|20)\d{2}\b/)
  if (yearMatch) return Date.parse(`${yearMatch[0]}-01-01`)

  return 0
}

const sortedCredentials = computed(() => {
  return [...credentials].sort((a, b) => {
    const diff = parseIssuedAtTimestamp(b.issuedAt) - parseIssuedAtTimestamp(a.issuedAt)
    if (diff !== 0) return diff
    return a.title.localeCompare(b.title)
  })
})

const featuredScopedCredentials = computed(() => {
  if (!props.featuredOnly) return sortedCredentials.value
  return sortedCredentials.value.filter(credential => credential.featured !== false)
})

const searchQuery = ref('')
const selectedProvider = ref('all')
const route = useRoute()
const router = useRouter()
const isSyncingFiltersFromRoute = ref(false)
let querySyncTimeout: ReturnType<typeof setTimeout> | null = null

const normalizeQueryParam = (
  value: string | string[] | null | undefined
): string => {
  if (Array.isArray(value)) return (value[0] ?? '').trim()
  if (typeof value === 'string') return value.trim()
  return ''
}

const normalizedSearchQuery = computed(() => searchQuery.value.trim().toLowerCase())

const providerOptions = computed(() => {
  const providers = new Set(
    featuredScopedCredentials.value
      .map(credential => credential.provider.trim())
      .filter(Boolean)
  )

  return [...providers].sort((a, b) => a.localeCompare(b))
})

const providerFilteredCredentials = computed(() => {
  if (selectedProvider.value === 'all') return featuredScopedCredentials.value
  return featuredScopedCredentials.value.filter(
    credential => credential.provider.trim() === selectedProvider.value
  )
})

const filteredCredentials = computed(() => {
  if (!normalizedSearchQuery.value) return providerFilteredCredentials.value

  return providerFilteredCredentials.value.filter((credential) => {
    const title = credential.title.toLowerCase()
    const provider = credential.provider.toLowerCase()
    return title.includes(normalizedSearchQuery.value) || provider.includes(normalizedSearchQuery.value)
  })
})

const visibleCount = ref(props.initialLoadCount)

const displayedCredentials = computed(() => {
  if (props.enableLoadMore) {
    return filteredCredentials.value.slice(0, visibleCount.value)
  }

  if (props.limit <= 0) return filteredCredentials.value
  return filteredCredentials.value.slice(0, props.limit)
})

const showViewAllLink = computed(() => {
  if (props.enableLoadMore) return false
  if (!props.showViewAll || props.limit <= 0 || credentials.length === 0) return false

  // On featured-only previews (home), keep this link visible if there are additional
  // non-featured credentials available on the full credentials page.
  if (props.featuredOnly) {
    return credentials.length > displayedCredentials.value.length
  }

  return filteredCredentials.value.length > displayedCredentials.value.length
})

const canLoadMore = computed(() => {
  if (!props.enableLoadMore) return false
  return displayedCredentials.value.length < filteredCredentials.value.length
})

const hasActiveRefine = computed(() => {
  return selectedProvider.value !== 'all' || normalizedSearchQuery.value.length > 0
})

const resolvedEmptyMessage = computed(() => {
  if (hasActiveRefine.value) return 'No credentials match your current search.'
  return props.emptyMessage
})

const brokenThumbnailIds = ref<Record<string, boolean>>({})
const selectedCredential = ref<Credential | null>(null)
const previewImageLoadFailed = ref(false)

const markThumbnailBroken = (credentialId: string) => {
  brokenThumbnailIds.value[credentialId] = true
}

const isPdfUrl = (url: string): boolean => {
  return url.toLowerCase().split('?')[0]?.endsWith('.pdf') === true
}

const isCloudinaryImageUploadUrl = (url: string): boolean => {
  return url.includes('res.cloudinary.com') && url.includes('/image/upload/')
}

const toCloudinaryPdfThumbnailUrl = (url: string): string => {
  try {
    const parsed = new URL(url)
    if (!parsed.pathname.includes('/image/upload/')) return url

    parsed.pathname = parsed.pathname.replace(
      '/image/upload/',
      '/image/upload/pg_1,f_jpg,q_auto,w_480/'
    )

    return parsed.toString()
  } catch {
    return url.replace('/image/upload/', '/image/upload/pg_1,f_jpg,q_auto,w_480/')
  }
}

const toCloudinaryPdfPreviewUrl = (url: string): string => {
  try {
    const parsed = new URL(url)
    if (!parsed.pathname.includes('/image/upload/')) return url

    parsed.pathname = parsed.pathname.replace(
      '/image/upload/',
      '/image/upload/pg_1,f_jpg,q_auto,w_1600/'
    )

    return parsed.toString()
  } catch {
    return url.replace('/image/upload/', '/image/upload/pg_1,f_jpg,q_auto,w_1600/')
  }
}

const thumbnailUrlFor = (credential: Credential): string | null => {
  const sourceUrl = credential.imageUrl?.trim()
  if (!sourceUrl) return null

  if (isPdfUrl(sourceUrl)) {
    if (!isCloudinaryImageUploadUrl(sourceUrl)) return null
    return toCloudinaryPdfThumbnailUrl(sourceUrl)
  }

  return sourceUrl
}

const previewUrlFor = (credential: Credential | null): string | null => {
  const sourceUrl = credential?.imageUrl?.trim()
  if (!sourceUrl) return null

  if (isPdfUrl(sourceUrl) && isCloudinaryImageUploadUrl(sourceUrl)) {
    return toCloudinaryPdfPreviewUrl(sourceUrl)
  }

  return sourceUrl
}

const openPreview = (credential: Credential) => {
  if (!credential.imageUrl) return
  selectedCredential.value = credential
}

const closePreview = () => {
  selectedCredential.value = null
}

const shouldUseIframePreview = computed(() => {
  const sourceUrl = selectedCredential.value?.imageUrl?.trim()
  if (!sourceUrl || !isPdfUrl(sourceUrl)) return false
  return !isCloudinaryImageUploadUrl(sourceUrl)
})

const previewUrl = computed(() => previewUrlFor(selectedCredential.value))

watch(selectedCredential, () => {
  previewImageLoadFailed.value = false
})

watch(providerOptions, (providers) => {
  if (selectedProvider.value !== 'all' && !providers.includes(selectedProvider.value)) {
    selectedProvider.value = 'all'
  }
})

watch(
  () => [route.query[props.querySearchKey], route.query[props.queryProviderKey]],
  ([rawSearch, rawProvider]) => {
    if (!props.showRefineControls || !props.syncFiltersToQuery) return

    const nextSearch = normalizeQueryParam(rawSearch as string | string[] | null | undefined)
    const providerFromQuery = normalizeQueryParam(rawProvider as string | string[] | null | undefined)
    const nextProvider = providerFromQuery || 'all'

    if (searchQuery.value === nextSearch && selectedProvider.value === nextProvider) return

    isSyncingFiltersFromRoute.value = true
    searchQuery.value = nextSearch
    selectedProvider.value = nextProvider
    isSyncingFiltersFromRoute.value = false
  },
  { immediate: true }
)

watch(filteredCredentials, () => {
  if (!props.enableLoadMore) return
  visibleCount.value = props.initialLoadCount
})

const syncFiltersToRouteQuery = () => {
  if (!props.showRefineControls || !props.syncFiltersToQuery || !import.meta.client) return
  if (isSyncingFiltersFromRoute.value) return

  const nextSearch = searchQuery.value.trim()
  const nextProvider = selectedProvider.value === 'all' ? '' : selectedProvider.value.trim()
  const currentSearch = normalizeQueryParam(route.query[props.querySearchKey] as string | string[] | null | undefined)
  const currentProvider = normalizeQueryParam(route.query[props.queryProviderKey] as string | string[] | null | undefined)

  if (currentSearch === nextSearch && currentProvider === nextProvider) return

  const nextQuery = {
    ...route.query
  } as Record<string, string | string[] | null | undefined>

  if (nextSearch) nextQuery[props.querySearchKey] = nextSearch
  else delete nextQuery[props.querySearchKey]

  if (nextProvider) nextQuery[props.queryProviderKey] = nextProvider
  else delete nextQuery[props.queryProviderKey]

  router.replace({ query: nextQuery })
}

watch([searchQuery, selectedProvider], () => {
  if (!props.showRefineControls || !props.syncFiltersToQuery) return

  if (querySyncTimeout) clearTimeout(querySyncTimeout)
  querySyncTimeout = setTimeout(() => {
    syncFiltersToRouteQuery()
  }, 180)
})

const loadMoreCredentials = () => {
  visibleCount.value += props.loadMoreStep
}

const clearRefine = () => {
  searchQuery.value = ''
  selectedProvider.value = 'all'
}

onUnmounted(() => {
  if (querySyncTimeout) clearTimeout(querySyncTimeout)
})

const resolveProviderIcon = (credential: Credential): string => {
  if (credential.providerIcon) return credential.providerIcon

  const provider = credential.provider.trim().toLowerCase()
  if (provider.includes('codecademy')) return 'simple-icons:codecademy'
  if (provider.includes('udemy')) return 'simple-icons:udemy'
  if (provider.includes('coursera')) return 'simple-icons:coursera'
  if (provider.includes('freecodecamp')) return 'simple-icons:freecodecamp'
  if (provider.includes('google')) return 'simple-icons:google'
  if (provider.includes('aws') || provider.includes('amazon')) return 'simple-icons:amazonwebservices'
  if (provider.includes('meta')) return 'simple-icons:meta'
  if (provider.includes('openclassrooms')) return 'simple-icons:openclassrooms'

  return 'lucide:award'
}
</script>

<style scoped>
.credential-card {
  background: rgb(255 255 255 / 0.8);
  border: 1px solid rgb(229 231 235);
  border-radius: 0.75rem;
  padding: 0.8rem 0.9rem;
  min-height: 7.75rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.credential-card-large {
  padding: 1.1rem 1.2rem;
  border-radius: 0.95rem;
  height: 13.5rem;
}

.credential-card:hover {
  border-color: rgb(245 71 71 / 0.45);
  box-shadow: 0 8px 16px rgb(0 0 0 / 0.12);
  transform: translateY(-1px);
}

.dark .credential-card {
  background: rgb(24 24 27 / 0.9);
  border-color: rgb(63 63 70);
}

.dark .credential-card:hover {
  border-color: rgb(245 71 71 / 0.55);
  box-shadow: 0 8px 16px rgb(0 0 0 / 0.25);
}

.credential-verify-link {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  color: var(--color-primary);
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 0.375rem;
  padding: 0.15rem 0.25rem;
  transition: color 0.2s ease, background-color 0.2s ease;
}

.credential-verify-link:hover {
  color: rgb(185 28 28);
  background: rgb(254 242 242);
}

.credential-verify-link:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.22);
}

.credential-thumb-button {
  width: 6.1rem;
  position: relative;
  align-self: stretch;
  height: auto;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid rgb(229 231 235);
  background: rgb(250 250 250);
  flex-shrink: 0;
  cursor: pointer;
}

.credential-thumb-button-large {
  width: 8.4rem;
}

.credential-title {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.credential-thumb-button:disabled {
  cursor: not-allowed;
}

.credential-thumb-button:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.28);
}

.credential-thumb-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.25s ease;
}

.credential-thumb-button:hover .credential-thumb-image {
  transform: scale(1.06);
}

.credential-thumb-fallback {
  width: 100%;
  height: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgb(254 226 226);
  color: var(--color-primary);
  background: rgb(254 242 242);
}

.credential-thumb-overlay {
  position: absolute;
  inset: auto 0 0 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.2rem 0.35rem;
  font-size: 0.65rem;
  font-weight: 700;
  color: white;
  background: rgb(0 0 0 / 0.55);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.credential-thumb-button:hover .credential-thumb-overlay {
  opacity: 1;
}

.credential-preview-surface {
  width: 100%;
  height: min(72vh, 640px);
  border: 1px solid rgb(229 231 235);
  border-radius: 0.75rem;
  background: rgb(250 250 250);
  overflow: hidden;
}

.credential-preview-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  background: rgb(250 250 250);
}

.credential-preview-document {
  width: 100%;
  height: 100%;
  border: 0;
  background: white;
}

.dark .credential-thumb-button {
  border-color: rgb(63 63 70);
  background: rgb(24 24 27);
}

.dark .credential-thumb-fallback {
  border-color: rgb(127 29 29 / 0.55);
  background: rgb(69 10 10 / 0.45);
}

.dark .credential-preview-surface {
  border-color: rgb(63 63 70);
  background: rgb(24 24 27);
}
</style>
