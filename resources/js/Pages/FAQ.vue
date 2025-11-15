<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { ChevronDown, HelpCircle, Search, Sparkles, ArrowLeft } from 'lucide-vue-next'
import { useThreeJsFox } from '@/composables/useThreeJsFox'
import NavigationHeader from '@/components/NavigationHeader.vue'
import { wTrans } from 'laravel-vue-i18n'

/**
 * FAQ PAGE WITH ANIMATED FOX
 * Frequently Asked Questions about dysgraphia and our services
 */

// Initialize Three.js animated fox
useThreeJsFox('three-fox-container')

interface FAQ {
  id: number
  question: string
  answer: string
  category: string
}

const searchQuery = ref('')
const openItems = ref<number[]>([])

const faqs: FAQ[] = [
  {
    id: 1,
    category: 'About Dysgraphia',
    question: 'What is dysgraphia?',
    answer: 'Dysgraphia is a learning difference that primarily affects writing abilities. It can involve difficulties with letter formation, spacing, spelling, and organizing thoughts on paper. Dysgraphia is neurological in origin and is not related to intelligence or laziness.'
  },
  {
    id: 2,
    category: 'About Dysgraphia',
    question: 'What are the signs of dysgraphia?',
    answer: 'Common signs include: inconsistent letter formation and spacing, difficulty with handwriting despite adequate instruction, slow writing speed, poor spelling, trouble organizing thoughts on paper, awkward pencil grip, and fatigue when writing.'
  },
  {
    id: 3,
    category: 'About Dysgraphia',
    question: 'Can dysgraphia be cured?',
    answer: 'While dysgraphia is a lifelong condition, its effects can be significantly reduced through proper support, therapy, and accommodations. Many individuals with dysgraphia develop successful strategies and go on to excel in their education and careers.'
  },
  {
    id: 4,
    category: 'Getting Help',
    question: 'How do I know if my child needs help?',
    answer: 'If your child struggles significantly with writing compared to their peers, shows frustration or avoidance of writing tasks, or has handwriting that is notably difficult to read despite practice, consider consulting a specialist for an evaluation.'
  },
  {
    id: 5,
    category: 'Getting Help',
    question: 'What happens during an assessment?',
    answer: 'A comprehensive dysgraphia assessment typically includes evaluation of fine motor skills, visual-motor integration, writing samples, spelling tests, and sometimes cognitive assessments. The specialist will create a detailed profile to guide treatment.'
  },
  {
    id: 6,
    category: 'Getting Help',
    question: 'How long does therapy take?',
    answer: 'The duration of therapy varies depending on individual needs and severity. Some children show improvement in a few months, while others may benefit from ongoing support throughout their school years. Progress is monitored regularly and plans are adjusted accordingly.'
  },
  {
    id: 7,
    category: 'Using Our Platform',
    question: 'How do I find a specialist?',
    answer: 'You can browse our directory of specialists by location, specialty, or use our search function. Each specialist profile includes their qualifications, experience, and availability. You can also view them on our interactive map.'
  },
  {
    id: 8,
    category: 'Using Our Platform',
    question: 'How do I book an appointment?',
    answer: 'Once you find a specialist, click "Book Appointment" on their profile. You\'ll be able to select available dates and times, provide information about your child, and complete the booking. You\'ll receive confirmation details via email.'
  },
  {
    id: 9,
    category: 'Using Our Platform',
    question: 'Are all specialists verified?',
    answer: 'Yes, all specialists on our platform are verified professionals with appropriate credentials and experience in dysgraphia support. We carefully review qualifications before adding providers to our network.'
  },
  {
    id: 10,
    category: 'Appointments & Payment',
    question: 'What should I bring to the first appointment?',
    answer: 'Bring any previous assessments or school reports, examples of your child\'s writing, and a list of concerns or questions. The specialist may also request medical history information.'
  },
  {
    id: 11,
    category: 'Appointments & Payment',
    question: 'How much do sessions cost?',
    answer: 'Fees vary by specialist and are displayed on each provider\'s profile. Many specialists offer sliding scale fees or payment plans. Some insurance plans may cover dysgraphia therapy - check with your provider.'
  },
  {
    id: 12,
    category: 'Appointments & Payment',
    question: 'Can I cancel or reschedule?',
    answer: 'Yes, you can manage your appointments through your account. Please review the individual specialist\'s cancellation policy, as some may require 24-48 hours notice.'
  }
]

const categories = [...new Set(faqs.map(faq => faq.category))]

const filteredFAQs = ref(faqs)

const toggleItem = (id: number) => {
  const index = openItems.value.indexOf(id)
  if (index > -1) {
    openItems.value.splice(index, 1)
  } else {
    openItems.value.push(id)
  }
}

const handleSearch = () => {
  if (!searchQuery.value) {
    filteredFAQs.value = faqs
    return
  }

  const query = searchQuery.value.toLowerCase()
  filteredFAQs.value = faqs.filter(faq => 
    faq.question.toLowerCase().includes(query) ||
    faq.answer.toLowerCase().includes(query) ||
    faq.category.toLowerCase().includes(query)
  )
}
</script>

<template>
  <Head title="FAQ - Dysgraphia Support" />

  <div class="min-h-screen bg-gradient-to-b from-gray-50 via-purple-50/30 to-white dark:from-gray-900 dark:via-purple-900/10 dark:to-gray-800">
    <!-- Navigation -->
    <NavigationHeader />

    <!-- Hero Section with Animated Fox -->
    <section class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white py-24 overflow-hidden">
      <!-- Three.js Fox Animation -->
      <div id="three-fox-container" class="absolute inset-0 opacity-40"></div>
      
      <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
          <!-- Breadcrumb -->
          <Link href="/" class="inline-flex items-center gap-2 text-indigo-100 hover:text-white mb-6 transition-colors">
            <ArrowLeft class="w-4 h-4" />
            <span>Back to Home</span>
          </Link>

          <div class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full mb-6">
            <Sparkles class="w-5 h-5" />
            <span class="text-sm font-semibold">Watch our friendly fox guide you!</span>
          </div>

          <HelpCircle class="w-20 h-20 mx-auto mb-6 animate-bounce" />
          <h1 class="text-6xl md:text-7xl font-black mb-6 animate-fade-in">
            Frequently Asked
            <span class="block text-yellow-300">Questions</span>
          </h1>
          <p class="text-xl md:text-2xl text-indigo-100 mb-10 leading-relaxed">
            Find answers to common questions about dysgraphia and our services
          </p>

          <!-- Enhanced Search -->
          <div class="relative max-w-2xl mx-auto group">
            <div class="absolute -inset-1 bg-gradient-to-r from-yellow-400 to-pink-400 rounded-full opacity-30 group-hover:opacity-50 blur transition-opacity"></div>
            <div class="relative">
              <Search class="absolute left-6 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" />
              <input 
                v-model="searchQuery"
                @input="handleSearch"
                type="text" 
                placeholder="Ask me anything about dysgraphia..."
                class="w-full pl-16 pr-6 py-5 rounded-full text-gray-900 text-lg focus:outline-none focus:ring-4 focus:ring-yellow-300 shadow-2xl font-medium"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Decorative Wave -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
          <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="currentColor" class="text-gray-50 dark:text-gray-900"/>
        </svg>
      </div>
    </section>

    <!-- FAQs with Enhanced Design -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto">
          <div v-for="category in categories" :key="category" class="mb-16">
            <div class="flex items-center gap-4 mb-8">
              <div class="flex-1 h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent"></div>
              <h2 class="text-4xl font-black text-gray-900 dark:text-white px-6 py-3 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-2xl shadow-lg">
                {{ category }}
              </h2>
              <div class="flex-1 h-1 bg-gradient-to-r from-transparent via-purple-500 to-transparent"></div>
            </div>

            <div class="space-y-4">
              <div 
                v-for="faq in filteredFAQs.filter((f: FAQ) => f.category === category)" 
                :key="faq.id"
                class="group bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-2xl overflow-hidden transition-all duration-300 border-2 border-transparent hover:border-indigo-500"
              >
                <button 
                  @click="toggleItem(faq.id)"
                  class="w-full px-8 py-6 flex items-center justify-between text-left hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-900/20 dark:hover:to-purple-900/20 transition-all duration-300"
                >
                  <div class="flex items-center gap-4 flex-1">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg group-hover:scale-110 transition-transform">
                      {{ faq.id }}
                    </div>
                    <span class="font-bold text-lg text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                      {{ faq.question }}
                    </span>
                  </div>
                  <ChevronDown 
                    :class="[
                      'w-6 h-6 text-indigo-600 dark:text-indigo-400 transition-all duration-300',
                      openItems.includes(faq.id) && 'transform rotate-180'
                    ]"
                  />
                </button>

                <transition name="expand">
                  <div v-if="openItems.includes(faq.id)" class="px-8 pb-6">
                    <div class="pl-14 pr-4 pt-2">
                      <div class="h-1 w-full bg-gradient-to-r from-indigo-200 via-purple-200 to-pink-200 dark:from-indigo-800 dark:via-purple-800 dark:to-pink-800 rounded-full mb-4"></div>
                      <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-lg">
                        {{ faq.answer }}
                      </p>
                    </div>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <!-- No Results -->
          <div v-if="filteredFAQs.length === 0" class="text-center py-12">
            <Search class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No results found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Try a different search term</p>
            <button 
              @click="() => { searchQuery = ''; handleSearch() }"
              class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors"
            >
              Clear Search
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact CTA with Enhanced Design -->
    <section class="relative py-24 overflow-hidden">
      <!-- Gradient Background -->
      <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600"></div>
      
      <!-- Animated Shapes -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-10 left-10 w-32 h-32 bg-yellow-400 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-40 h-40 bg-pink-400 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-36 h-36 bg-blue-400 rounded-full blur-3xl animate-pulse" style="animation-delay: 0.5s;"></div>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full mb-6">
            <Sparkles class="w-5 h-5 text-yellow-300" />
            <span class="text-sm font-semibold text-white">Get Personalized Support</span>
          </div>

          <h2 class="text-4xl md:text-5xl font-black text-white mb-6">
            Still have questions?
          </h2>
          <p class="text-xl md:text-2xl text-indigo-100 mb-10 leading-relaxed">
            Our team is ready to provide personalized guidance. Reach out anytime!
          </p>
          
          <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <Link 
              href="/#contact"
              class="group px-10 py-5 bg-white text-indigo-600 rounded-2xl font-bold text-lg hover:shadow-2xl hover:scale-105 transition-all flex items-center justify-center gap-3 relative overflow-hidden"
            >
              <span class="absolute inset-0 bg-gradient-to-r from-yellow-100 to-pink-100 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
              <span class="relative z-10">Contact Us</span>
              <ChevronRight class="w-5 h-5 relative z-10 group-hover:translate-x-1 transition-transform" />
            </Link>
            
            <Link 
              href="/resources"
              class="group px-10 py-5 bg-transparent border-2 border-white text-white rounded-2xl font-bold text-lg hover:bg-white hover:text-indigo-600 transition-all flex items-center justify-center gap-3"
            >
              <span class="relative z-10">Browse Resources</span>
            </Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease-out;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
  overflow: hidden;
}

.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 500px;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.2;
    transform: scale(1);
  }
  50% {
    opacity: 0.3;
    transform: scale(1.1);
  }
}

.animate-pulse {
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
