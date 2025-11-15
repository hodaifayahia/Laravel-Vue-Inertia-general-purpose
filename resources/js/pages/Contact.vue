<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Phone, Mail, MapPin, Send } from 'lucide-vue-next'
import NavigationHeader from '@/components/NavigationHeader.vue'
import { wTrans } from 'laravel-vue-i18n'

/**
 * CONTACT PAGE
 * Get in touch with the Dysgraphia Support Platform
 */
</script>

<template>
  <Head :title="wTrans('contact.page_title')" />

  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <!-- Navigation -->
    <NavigationHeader />

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-5xl md:text-6xl font-bold mb-6">{{ wTrans('contact.get_in_touch') }}</h1>
          <p class="text-xl md:text-2xl text-indigo-100">
            {{ wTrans('contact.contact_description') }}
          </p>
        </div>
      </div>
    </section>

    <!-- Contact Content -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
          <div class="grid md:grid-cols-2 gap-12">
            <!-- Contact Information -->
            <div class="space-y-8">
              <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">{{ wTrans('contact.quick_contact') }}</h2>
              </div>

              <!-- Phone -->
              <div class="flex items-start gap-6 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center flex-shrink-0">
                  <Phone class="w-7 h-7 text-indigo-600" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ wTrans('contact.phone') }}</h3>
                  <p class="text-gray-600 dark:text-gray-400">{{ wTrans('contact.phone_number') }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">{{ wTrans('contact.phone_hours') }}</p>
                </div>
              </div>

              <!-- Email -->
              <div class="flex items-start gap-6 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center flex-shrink-0">
                  <Mail class="w-7 h-7 text-purple-600" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ wTrans('contact.email') }}</h3>
                  <p class="text-gray-600 dark:text-gray-400">{{ wTrans('contact.email_address') }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">{{ wTrans('contact.email_response_time') }}</p>
                </div>
              </div>

              <!-- Locations -->
              <div class="flex items-start gap-6 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                <div class="w-14 h-14 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center flex-shrink-0">
                  <MapPin class="w-7 h-7 text-pink-600" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ wTrans('contact.locations') }}</h3>
                  <p class="text-gray-600 dark:text-gray-400">{{ wTrans('contact.locations_description') }}</p>
                </div>
              </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-3xl p-8 shadow-xl h-fit sticky top-24">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ wTrans('contact.send_message') }}</h2>
              <form class="space-y-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ wTrans('contact.name_placeholder') }}</label>
                  <input 
                    type="text" 
                    :placeholder="wTrans('contact.name_placeholder')"
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ wTrans('contact.email_placeholder') }}</label>
                  <input 
                    type="email" 
                    :placeholder="wTrans('contact.email_placeholder')"
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Subject</label>
                  <input 
                    type="text" 
                    placeholder="What is this about?"
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Message</label>
                  <textarea 
                    :placeholder="wTrans('contact.message_placeholder')"
                    rows="5"
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none"
                  ></textarea>
                </div>

                <button 
                  type="submit"
                  class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all flex items-center justify-center gap-2 group"
                >
                  <span>{{ $t('contact.send_message') }}</span>
                  <Send class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <h2 class="text-4xl md:text-5xl font-bold mb-6">{{ wTrans('bookings.ready_to_book') }}</h2>
          <p class="text-xl text-indigo-100 mb-8">
            {{ wTrans('bookings.connect_specialist') }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link 
              href="/doctors"
              class="px-8 py-4 bg-white text-indigo-600 rounded-full font-semibold text-lg hover:bg-gray-100 transition-colors"
            >
              {{ wTrans('specialists.page_title') }}
            </Link>
            <Link 
              href="/appointments"
              class="px-8 py-4 bg-indigo-800 text-white rounded-full font-semibold text-lg hover:bg-indigo-900 transition-colors"
            >
              {{ wTrans('bookings.book_now') }}
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto text-center text-gray-400">
          <p>{{ wTrans('footer.copyright') }}</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Smooth transitions */
* {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
