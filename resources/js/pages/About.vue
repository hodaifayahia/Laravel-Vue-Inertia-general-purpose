<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Heart, Users, Target, Award, BookOpen, Lightbulb } from 'lucide-vue-next'
import NavigationHeader from '@/components/NavigationHeader.vue'
import { wTrans } from 'laravel-vue-i18n'

/**
 * ABOUT US PAGE
 * Information about the dysgraphia support platform
 */
</script>

<template>
  <Head :title="wTrans('about.page_title')" />

  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <!-- Navigation -->
    <NavigationHeader />

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-5xl md:text-6xl font-bold mb-6">{{ wTrans('about.hero_title') }}</h1>
          <p class="text-xl md:text-2xl text-indigo-100">
            {{ wTrans('about.hero_subtitle') }}
          </p>
        </div>
      </div>
    </section>

    <!-- Our Story -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
          <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">{{ wTrans('about.our_story_title') }}</h2>
            <p class="text-xl text-gray-600 dark:text-gray-400 leading-relaxed">
              {{ wTrans('about.our_story_text') }}
            </p>
          </div>

          <div class="prose prose-lg dark:prose-invert mx-auto">
            <p class="text-gray-600 dark:text-gray-400">
              {{ wTrans('about.our_story_content') }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Values -->
    <section class="py-20 bg-gray-50 dark:bg-gray-800">
      <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
          <h2 class="text-4xl font-bold text-center text-gray-900 dark:text-white mb-16">{{ wTrans('about.values_section_title') }}</h2>

          <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-lg">
              <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mb-6">
                <Heart class="w-8 h-8 text-indigo-600" />
              </div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ wTrans('about.compassion_title') }}</h3>
              <p class="text-gray-600 dark:text-gray-400">
                {{ wTrans('about.compassion_desc') }}
              </p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-lg">
              <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mb-6">
                <Target class="w-8 h-8 text-purple-600" />
              </div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ wTrans('about.excellence_title') }}</h3>
              <p class="text-gray-600 dark:text-gray-400">
                {{ wTrans('about.excellence_desc') }}
              </p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl p-8 shadow-lg">
              <div class="w-16 h-16 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center mb-6">
                <Users class="w-8 h-8 text-pink-600" />
              </div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ wTrans('about.accessibility_title') }}</h3>
              <p class="text-gray-600 dark:text-gray-400">
                {{ wTrans('about.accessibility_desc') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- What We Do -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
          <h2 class="text-4xl font-bold text-center text-gray-900 dark:text-white mb-16">{{ wTrans('about.what_we_do_title') }}</h2>

          <div class="grid md:grid-cols-2 gap-12">
            <div class="flex gap-6">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center">
                  <BookOpen class="w-6 h-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ wTrans('about.connect_title') }}</h3>
                <p class="text-gray-600 dark:text-gray-400">
                  {{ wTrans('about.connect_desc') }}
                </p>
              </div>
            </div>

            <div class="flex gap-6">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                  <Lightbulb class="w-6 h-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ wTrans('about.resources_title') }}</h3>
                <p class="text-gray-600 dark:text-gray-400">
                  {{ wTrans('about.resources_desc') }}
                </p>
              </div>
            </div>

            <div class="flex gap-6">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-pink-600 rounded-full flex items-center justify-center">
                  <Award class="w-6 h-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ wTrans('about.quality_title') }}</h3>
                <p class="text-gray-600 dark:text-gray-400">
                  {{ wTrans('about.quality_desc') }}
                </p>
              </div>
            </div>

            <div class="flex gap-6">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center">
                  <Users class="w-6 h-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ wTrans('about.community_title') }}</h3>
                <p class="text-gray-600 dark:text-gray-400">
                  {{ wTrans('about.community_desc') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
      <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <h2 class="text-4xl md:text-5xl font-bold mb-6">{{ wTrans('about.cta_title') }}</h2>
          <p class="text-xl text-indigo-100 mb-8">
            {{ wTrans('about.cta_subtitle') }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link 
              href="/doctors"
              class="px-8 py-4 bg-white text-indigo-600 rounded-full font-semibold text-lg hover:bg-gray-100 transition-colors"
            >
              {{ wTrans('about.find_specialist') }}
            </Link>
            <Link 
              href="/appointments"
              class="px-8 py-4 bg-indigo-800 text-white rounded-full font-semibold text-lg hover:bg-indigo-900 transition-colors"
            >
              {{ wTrans('about.book_appointment') }}
            </Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
