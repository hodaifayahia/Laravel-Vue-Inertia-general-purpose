<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { 
  BookOpen, 
  Download, 
  Video, 
  FileText, 
  ExternalLink,
  Search,
  Filter
} from 'lucide-vue-next'
import NavigationHeader from '@/components/NavigationHeader.vue'
import { wTrans } from 'laravel-vue-i18n'

/**
 * RESOURCES PAGE
 * Educational materials and resources for families
 */

interface Resource {
  id: number
  title: string
  description: string
  type: 'article' | 'video' | 'guide' | 'worksheet'
  category: string
  url?: string
  downloadable: boolean
}

const searchQuery = ref('')
const selectedCategory = ref('all')
const selectedType = ref('all')

const resources: Resource[] = [
  {
    id: 1,
    title: 'resources.understanding_dysgraphia_guide',
    description: 'resources.understanding_dysgraphia_desc',
    type: 'guide',
    category: 'Getting Started',
    downloadable: true
  },
  {
    id: 2,
    title: 'resources.handwriting_worksheets',
    description: 'resources.handwriting_worksheets_desc',
    type: 'worksheet',
    category: 'Practice Materials',
    downloadable: true
  },
  {
    id: 3,
    title: 'resources.accommodations_school',
    description: 'resources.accommodations_school_desc',
    type: 'article',
    category: 'Education',
    downloadable: true
  },
  {
    id: 4,
    title: 'resources.fine_motor_exercises',
    description: 'resources.fine_motor_exercises_desc',
    type: 'video',
    category: 'Practice Materials',
    url: '#',
    downloadable: false
  },
  {
    id: 5,
    title: 'resources.assistive_technology',
    description: 'resources.assistive_technology_desc',
    type: 'article',
    category: 'Technology',
    downloadable: false
  },
  {
    id: 6,
    title: 'resources.writing_routine',
    description: 'resources.writing_routine_desc',
    type: 'worksheet',
    category: 'Practice Materials',
    downloadable: true
  },
  {
    id: 7,
    title: 'resources.working_with_teacher',
    description: 'resources.working_with_teacher_desc',
    type: 'guide',
    category: 'Education',
    downloadable: true
  },
  {
    id: 8,
    title: 'resources.success_stories',
    description: 'resources.success_stories_desc',
    type: 'article',
    category: 'Support',
    downloadable: false
  },
  {
    id: 9,
    title: 'resources.multisensory_writing',
    description: 'resources.multisensory_writing_desc',
    type: 'video',
    category: 'Strategies',
    url: '#',
    downloadable: false
  },
  {
    id: 10,
    title: 'resources.iep_504_guide',
    description: 'resources.iep_504_guide_desc',
    type: 'guide',
    category: 'Education',
    downloadable: true
  },
  {
    id: 11,
    title: 'resources.pencil_grip',
    description: 'resources.pencil_grip_desc',
    type: 'article',
    category: 'Strategies',
    downloadable: true
  },
  {
    id: 12,
    title: 'resources.building_confidence',
    description: 'resources.building_confidence_desc',
    type: 'article',
    category: 'Support',
    downloadable: false
  }
]

const categories = [...new Set(resources.map(r => r.category))].sort()
const types = ['article', 'video', 'guide', 'worksheet']

const filteredResources = ref(resources)

const applyFilters = () => {
  let result = [...resources]

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(r => 
      r.title.toLowerCase().includes(query) ||
      r.description.toLowerCase().includes(query) ||
      r.category.toLowerCase().includes(query)
    )
  }

  // Category filter
  if (selectedCategory.value !== 'all') {
    result = result.filter(r => r.category === selectedCategory.value)
  }

  // Type filter
  if (selectedType.value !== 'all') {
    result = result.filter(r => r.type === selectedType.value)
  }

  filteredResources.value = result
}

const getTypeIcon = (type: string) => {
  switch (type) {
    case 'article': return FileText
    case 'video': return Video
    case 'guide': return BookOpen
    case 'worksheet': return FileText
    default: return FileText
  }
}

const getTypeColor = (type: string) => {
  switch (type) {
    case 'article': return 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400'
    case 'video': return 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400'
    case 'guide': return 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400'
    case 'worksheet': return 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400'
    default: return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
  }
}
</script>

<template>
  <Head :title="wTrans('resources.page_title')" />

  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <!-- Navigation -->
    <NavigationHeader />

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <BookOpen class="w-16 h-16 mx-auto mb-6" />
          <h1 class="text-5xl md:text-6xl font-bold mb-6">{{ wTrans('resources.our_resources') }}</h1>
          <p class="text-xl md:text-2xl text-indigo-100 mb-8">
            {{ wTrans('resources.resources_subtitle') }}
          </p>

          <!-- Search -->
          <div class="relative max-w-2xl mx-auto">
            <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" />
            <input 
              v-model="searchQuery"
              @input="applyFilters"
              type="text" 
              :placeholder="wTrans('resources.search_resources')"
              class="w-full pl-14 pr-4 py-4 rounded-full text-gray-900 text-lg focus:outline-none focus:ring-4 focus:ring-indigo-300"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="py-12">
      <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Filters Sidebar -->
          <aside class="lg:w-80 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-24">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <Filter class="w-6 h-6" />
                {{ wTrans('resources.filter_by_specialization') }}
              </h2>

              <!-- Category Filter -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                  {{ wTrans('resources.guides') }}
                </label>
                <select 
                  v-model="selectedCategory"
                  @change="applyFilters"
                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:border-indigo-500 transition-colors"
                >
                  <option value="all">{{ wTrans('resources.all_specializations') }}</option>
                  <option v-for="category in categories" :key="category" :value="category">
                    {{ category }}
                  </option>
                </select>
              </div>

              <!-- Type Filter -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                  {{ wTrans('resources.tools') }}
                </label>
                <select 
                  v-model="selectedType"
                  @change="applyFilters"
                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:border-indigo-500 transition-colors"
                >
                  <option value="all">{{ wTrans('resources.all_specializations') }}</option>
                  <option value="article">{{ wTrans('resources.articles') }}</option>
                  <option value="video">{{ wTrans('resources.videos') }}</option>
                  <option value="guide">{{ wTrans('resources.guides') }}</option>
                  <option value="worksheet">{{ wTrans('resources.tools') }}</option>
                </select>
              </div>
            </div>
          </aside>

          <!-- Resources Grid -->
          <main class="flex-1">
            <div class="mb-6">
              <p class="text-gray-700 dark:text-gray-300">
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ filteredResources.length }}</span>
                <span class="ml-2">{{ wTrans('resources.read_more') }}</span>
              </p>
            </div>

            <!-- Empty State -->
            <div v-if="filteredResources.length === 0" class="text-center py-20">
              <Search class="w-20 h-20 mx-auto text-gray-400 mb-4" />
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ wTrans('resources.page_title') }}</h3>
              <p class="text-gray-600 dark:text-gray-400 mb-6">{{ wTrans('resources.tools') }}</p>
              <button 
                @click="() => { searchQuery = ''; selectedCategory = 'all'; selectedType = 'all'; applyFilters() }"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors"
              >
                {{ wTrans('resources.download') }}
              </button>
            </div>

            <!-- Resources List -->
            <div v-else class="grid md:grid-cols-2 gap-6">
              <div 
                v-for="resource in filteredResources" 
                :key="resource.id"
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all overflow-hidden"
              >
                <div class="p-6">
                  <!-- Header -->
                  <div class="flex items-start justify-between mb-4">
                    <component 
                      :is="getTypeIcon(resource.type)" 
                      class="w-8 h-8 text-indigo-600 flex-shrink-0"
                    />
                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getTypeColor(resource.type)]">
                      {{ resource.type.charAt(0).toUpperCase() + resource.type.slice(1) }}
                    </span>
                  </div>

                  <!-- Title & Description -->
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                    {{ wTrans(resource.title) }}
                  </h3>
                  <p class="text-gray-600 dark:text-gray-400 mb-4">
                    {{ wTrans(resource.description) }}
                  </p>

                  <!-- Category Badge -->
                  <div class="flex items-center gap-2 mb-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ resource.category }}</span>
                  </div>

                  <!-- Actions -->
                  <div class="flex gap-2">
                    <button 
                      v-if="resource.downloadable"
                      class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all flex items-center justify-center gap-2"
                    >
                      <Download class="w-5 h-5" />
                      {{ wTrans('resources.download') }}
                    </button>
                    <a 
                      v-else-if="resource.url"
                      :href="resource.url"
                      target="_blank"
                      class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all flex items-center justify-center gap-2"
                    >
                      <ExternalLink class="w-5 h-5" />
                      {{ wTrans('resources.view_resource') }}
                    </a>
                    <button 
                      v-else
                      class="flex-1 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                      {{ wTrans('resources.view_details') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </main>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <h2 class="text-4xl md:text-5xl font-bold mb-6">{{ wTrans('resources.need_personalized_support') }}</h2>
          <p class="text-xl text-indigo-100 mb-8">
            {{ wTrans('resources.support_description') }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link 
              href="/doctors"
              class="px-8 py-4 bg-white text-indigo-600 rounded-full font-semibold text-lg hover:bg-gray-100 transition-colors"
            >
              {{ wTrans('resources.find_specialist') }}
            </Link>
            <Link 
              href="/appointments"
              class="px-8 py-4 bg-indigo-800 text-white rounded-full font-semibold text-lg hover:bg-indigo-900 transition-colors"
            >
              {{ wTrans('bookings.book_appointment') }}
            </Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
