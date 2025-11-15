<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Menu, X } from 'lucide-vue-next'
import LocaleSelector from './LocaleSelector.vue'
import ThemeToggle from './ThemeToggle.vue'

interface Props {
  showBookButton?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showBookButton: true
})

const mobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const scrollToAnchor = (anchorId: string) => {
  const element = document.getElementById(anchorId)
  if (element) {
    // Close mobile menu if open
    mobileMenuOpen.value = false
    
    // Scroll to element with offset for fixed header
    const yOffset = -80 // Adjust based on header height
    const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset
    window.scrollTo({ top: y, behavior: 'smooth' })
  }
}
</script>

<template>
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl shadow-sm border-b border-gray-200/50 dark:border-gray-800/50">
    <div class="container mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <!-- Logo Section -->
        <div class="flex items-center gap-2">
          <Link href="/" class="text-2xl font-bold bg-gradient-to-r from-rose-600 to-amber-600 dark:from-rose-400 dark:to-amber-400 bg-clip-text text-transparent hover:opacity-80 transition">
            {{ $t('welcome.page_title') }}
          </Link>
        </div>
        
        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center gap-6">
          <Link href="/" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors text-sm font-medium">
            {{ $t('sidebar.main') }}
          </Link>
          <Link href="/doctors" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors text-sm font-medium">
            {{ $t('specialists.page_title') }}
          </Link>
          <Link href="/about" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors text-sm font-medium">
            {{ $t('about.mission_title') }}
          </Link>
          <Link href="/resources" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors text-sm font-medium">
            {{ $t('resources.page_title') }}
          </Link>
          <Link href="/contact" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors text-sm font-medium">
            {{ $t('contact.page_title') }}
          </Link>
        </div>

        <!-- Right Side: Selectors & Mobile Menu Toggle -->
        <div class="flex items-center gap-3">
          <LocaleSelector />
          <ThemeToggle />
          
          <!-- Book Appointment Button (Desktop) -->
          <Link 
            v-if="showBookButton"
            href="/appointments" 
            class="hidden md:inline-block px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full hover:shadow-lg transition-all text-sm font-semibold"
          >
            {{ $t('bookings.book_appointment') }}
          </Link>
          
          <!-- Mobile Menu Button -->
          <button 
            @click="toggleMobileMenu"
            class="md:hidden p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition"
          >
            <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Mobile Navigation Menu -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-4"
      >
        <div 
          v-if="mobileMenuOpen"
          class="md:hidden mt-4 pb-4 space-y-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-md rounded-lg p-4 border border-gray-200/50 dark:border-gray-700/50"
        >
          <Link 
            href="/" 
            @click="closeMobileMenu"
            class="block px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition"
          >
            {{ $t('sidebar.main') }}
          </Link>
          <Link 
            href="/doctors" 
            @click="closeMobileMenu"
            class="block px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition font-semibold"
          >
            {{ $t('specialists.page_title') }}
          </Link>
          <Link 
            href="/about" 
            @click="closeMobileMenu"
            class="block px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition"
          >
            {{ $t('about.mission_title') }}
          </Link>
          <Link 
            href="/resources" 
            @click="closeMobileMenu"
            class="block px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition"
          >
            {{ $t('resources.page_title') }}
          </Link>
          <Link 
            href="/contact" 
            @click="closeMobileMenu"
            class="block px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition"
          >
            {{ $t('contact.page_title') }}
          </Link>
          <Link 
            v-if="showBookButton"
            href="/appointments"
            @click="closeMobileMenu"
            class="block px-4 py-2 mt-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded hover:shadow-lg transition-all"
          >
            {{ $t('bookings.book_appointment') }}
          </Link>
        </div>
      </transition>
    </div>
  </nav>
</template>

<style scoped>
/* Smooth transitions */
* {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
