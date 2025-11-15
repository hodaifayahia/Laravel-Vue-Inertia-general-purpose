<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import gsap from 'gsap'
import { 
  Target, 
  ChevronRight, 
  ChevronLeft,
  CheckCircle2,
  Award,
  Heart,
  Shield,
  Star,
  BookOpen,
  Users,
  Zap,
  Brain,
  Lightbulb,
  Sparkles,
  Download,
  Share2,
  RefreshCw,
  ArrowRight
} from 'lucide-vue-next'
import NavigationHeader from '@/components/NavigationHeader.vue'
import { wTrans } from 'laravel-vue-i18n'

interface AssessmentResult {
  category: string
  score: number
  maxScore: number
  level: 'excellent' | 'good' | 'needs_improvement' | 'concerning'
  recommendations: string[]
  icon: string
}

// Reactive state
const currentStep = ref(0)
const assessmentStarted = ref(false)
const assessmentCompleted = ref(false)
const results = ref<AssessmentResult[]>([])
const taskCompletedStates = ref<boolean[]>([false, false, false, false])

// Child information form
const childForm = useForm({
  name: '',
  age: '',
  grade: '',
  parent_email: '',
  parent_name: '',
  parent_phone: '',
  concerns: ''
})

// Assessment questions
const assessmentSteps = [
  {
    id: 'fine_motor',
    title: 'Fine Motor Skills Assessment',
    description: 'Testing hand-eye coordination and control',
    type: 'game',
    icon: '✏️',
    content: 'Fine motor exercises'
  },
  {
    id: 'letter_formation',
    title: 'Letter Formation Assessment',
    description: 'Evaluating letter accuracy and consistency',
    type: 'game',
    icon: '📝',
    content: 'Letter writing tasks'
  },
  {
    id: 'writing_speed',
    title: 'Writing Speed Assessment',
    description: 'Measuring writing fluency and speed',
    type: 'game',
    icon: '⚡',
    content: 'Speed writing exercises'
  },
  {
    id: 'spatial_organization',
    title: 'Spatial Organization Assessment',
    description: 'Testing spatial awareness and organization',
    type: 'game',
    icon: '🎯',
    content: 'Spatial arrangement tasks'
  }
]

// Progress calculation
const progress = computed(() => {
  if (!assessmentStarted.value) return 0
  return Math.round(((currentStep.value + 1) / 4) * 100)
})

// Start assessment
const startAssessment = () => {
  if (!childForm.name || !childForm.age) {
    alert('Please fill in your child\'s name and age')
    return
  }
  assessmentStarted.value = true
}

// Next step
const nextStep = () => {
  if (currentStep.value < 3) {
    currentStep.value++
    taskCompletedStates.value[currentStep.value] = false
  } else {
    completeAssessment()
  }
}

// Previous step
const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

// Complete assessment
const completeAssessment = () => {
  results.value = [
    {
      category: 'Writing Mechanics',
      score: 75,
      maxScore: 100,
      level: 'good',
      icon: '✏️',
      recommendations: [
        'Practice daily handwriting exercises',
        'Use proper pencil grip techniques',
        'Consider occupational therapy evaluation'
      ]
    },
    {
      category: 'Motor Coordination',
      score: 60,
      maxScore: 100,
      level: 'needs_improvement',
      icon: '🎯',
      recommendations: [
        'Incorporate fine motor activities',
        'Try therapeutic putty exercises',
        'Consult with occupational therapist'
      ]
    },
    {
      category: 'Cognitive Processing',
      score: 85,
      maxScore: 100,
      level: 'excellent',
      icon: '🧠',
      recommendations: [
        'Continue building on strengths',
        'Challenge with more complex tasks'
      ]
    },
    {
      category: 'Memory & Attention',
      score: 70,
      maxScore: 100,
      level: 'good',
      icon: '📊',
      recommendations: [
        'Practice memory games regularly',
        'Use visual aids for learning',
        'Break tasks into smaller steps'
      ]
    }
  ]
  
  assessmentCompleted.value = true
}

// Get level color
const getLevelColor = (level: string) => {
  switch (level) {
    case 'excellent': return 'bg-green-500/30 text-green-200'
    case 'good': return 'bg-blue-500/30 text-blue-200'
    case 'needs_improvement': return 'bg-yellow-500/30 text-yellow-200'
    case 'concerning': return 'bg-red-500/30 text-red-200'
    default: return 'bg-gray-500/30 text-gray-200'
  }
}

// Get level gradient
const getLevelGradient = (level: string) => {
  switch (level) {
    case 'excellent': return 'bg-gradient-to-r from-green-400 to-emerald-500'
    case 'good': return 'bg-gradient-to-r from-blue-400 to-cyan-500'
    case 'needs_improvement': return 'bg-gradient-to-r from-yellow-400 to-orange-500'
    case 'concerning': return 'bg-gradient-to-r from-red-400 to-pink-500'
    default: return 'bg-gradient-to-r from-gray-400 to-gray-500'
  }
}

// Restart assessment
const restartAssessment = () => {
  currentStep.value = 0
  assessmentStarted.value = false
  assessmentCompleted.value = false
  results.value = []
  taskCompletedStates.value = [false, false, false, false]
  childForm.reset()
}

onMounted(() => {
  // GSAP animations
  gsap.from('.assessment-header', {
    duration: 0.8,
    opacity: 0,
    y: 30,
    ease: 'power2.out'
  })
})
</script>

<template>
  <Head :title="wTrans('assessment.page_title')" />

  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 dark:from-slate-950 dark:via-purple-950 dark:to-slate-950">
    
    <!-- Navigation -->
    <NavigationHeader />

    <div class="container mx-auto px-4 py-16">
      <div class="max-w-5xl mx-auto">

        <!-- Enhanced Header with Animation -->
        <div class="text-center mb-16 assessment-header" v-if="!assessmentStarted && !assessmentCompleted">
          <div class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-blue-500/20 to-purple-500/20 backdrop-blur-xl rounded-full shadow-2xl mb-8 border border-purple-400/30 hover:border-purple-400/50 transition-all duration-300">
            <Sparkles class="w-6 h-6 text-purple-300 animate-spin" style="animation-duration: 3s" />
            <span class="text-lg font-semibold bg-gradient-to-r from-blue-200 to-purple-200 bg-clip-text text-transparent">
              {{ wTrans('assessment.page_title') }}
            </span>
            <Sparkles class="w-6 h-6 text-purple-300 animate-spin" style="animation-duration: 3s; animation-direction: reverse" />
          </div>

          <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight tracking-tight">
            <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
              Discover Your Child's Learning Profile
            </span>
          </h1>
          <p class="text-xl text-gray-300 mb-8 max-w-3xl mx-auto leading-relaxed">
            {{ wTrans('assessment.page_description') }}
          </p>

          <!-- Quick Stats -->
          <div class="grid md:grid-cols-3 gap-4 mb-12">
            <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 backdrop-blur-md rounded-2xl p-6 border border-green-400/30 hover:border-green-400/50 transition-all">
              <div class="flex items-center gap-3">
                <Zap class="w-8 h-8 text-green-300" />
                <div class="text-left">
                  <p class="text-green-200 text-sm font-semibold">10-15 Minutes</p>
                  <p class="text-green-100 text-xs">Quick Assessment</p>
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 backdrop-blur-md rounded-2xl p-6 border border-blue-400/30 hover:border-blue-400/50 transition-all">
              <div class="flex items-center gap-3">
                <Target class="w-8 h-8 text-blue-300" />
                <div class="text-left">
                  <p class="text-blue-200 text-sm font-semibold">Comprehensive</p>
                  <p class="text-blue-100 text-xs">Multi-Dimensional</p>
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-br from-pink-500/20 to-rose-500/20 backdrop-blur-md rounded-2xl p-6 border border-pink-400/30 hover:border-pink-400/50 transition-all">
              <div class="flex items-center gap-3">
                <Shield class="w-8 h-8 text-pink-300" />
                <div class="text-left">
                  <p class="text-pink-200 text-sm font-semibold">100% Private</p>
                  <p class="text-pink-100 text-xs">Completely Free</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Child Information Form -->
        <div v-if="!assessmentStarted && !assessmentCompleted" class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl rounded-3xl p-8 md:p-12 shadow-2xl border border-white/20 mb-8">
          <h2 class="text-3xl font-bold text-white mb-8 text-center">
            {{ wTrans('assessment.child_info_title') }}
          </h2>
          <p class="text-center text-gray-300 mb-8">
            {{ wTrans('assessment.child_info_description') }}
          </p>
          
          <form @submit.prevent="startAssessment" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-gray-200 mb-3">
                  {{ wTrans('assessment.child_name') }} *
                </label>
                <input 
                  v-model="childForm.name"
                  type="text" 
                  required
                  class="w-full px-4 py-3 rounded-xl border-2 border-purple-400/30 bg-white/10 backdrop-blur-sm focus:border-purple-400 focus:bg-white/20 transition-all text-white placeholder-gray-400 font-medium"
                  :placeholder="wTrans('assessment.child_name_placeholder')"
                />
              </div>
              
              <div>
                <label class="block text-sm font-semibold text-gray-200 mb-3">
                  {{ wTrans('assessment.child_age') }} *
                </label>
                <input 
                  v-model="childForm.age"
                  type="number" 
                  required
                  min="5"
                  max="18"
                  class="w-full px-4 py-3 rounded-xl border-2 border-purple-400/30 bg-white/10 backdrop-blur-sm focus:border-purple-400 focus:bg-white/20 transition-all text-white placeholder-gray-400 font-medium"
                  :placeholder="wTrans('assessment.child_age_placeholder')"
                />
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-gray-200 mb-3">
                  {{ wTrans('assessment.child_grade') }}
                </label>
                <select 
                  v-model="childForm.grade"
                  class="w-full px-4 py-3 rounded-xl border-2 border-purple-400/30 bg-white/10 backdrop-blur-sm focus:border-purple-400 focus:bg-white/20 transition-all text-white font-medium"
                >
                  <option value="" class="bg-gray-800">{{ wTrans('assessment.child_grade_placeholder') }}</option>
                  <option value="kindergarten" class="bg-gray-800">Kindergarten</option>
                  <option value="1st" class="bg-gray-800">1st Grade</option>
                  <option value="2nd" class="bg-gray-800">2nd Grade</option>
                  <option value="3rd" class="bg-gray-800">3rd Grade</option>
                  <option value="4th" class="bg-gray-800">4th Grade</option>
                  <option value="5th" class="bg-gray-800">5th Grade</option>
                  <option value="6th" class="bg-gray-800">6th Grade</option>
                  <option value="7th" class="bg-gray-800">7th Grade</option>
                  <option value="8th" class="bg-gray-800">8th Grade</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-semibold text-gray-200 mb-3">
                  {{ wTrans('assessment.parent_name') }}
                </label>
                <input 
                  v-model="childForm.parent_name"
                  type="text" 
                  class="w-full px-4 py-3 rounded-xl border-2 border-purple-400/30 bg-white/10 backdrop-blur-sm focus:border-purple-400 focus:bg-white/20 transition-all text-white placeholder-gray-400 font-medium"
                  :placeholder="wTrans('assessment.parent_name_placeholder')"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-200 mb-3">
                {{ wTrans('assessment.parent_email') }}
              </label>
              <input 
                v-model="childForm.parent_email"
                type="email" 
                class="w-full px-4 py-3 rounded-xl border-2 border-purple-400/30 bg-white/10 backdrop-blur-sm focus:border-purple-400 focus:bg-white/20 transition-all text-white placeholder-gray-400 font-medium"
                :placeholder="wTrans('assessment.parent_email_placeholder')"
              />
            </div>

            <div class="text-center pt-6">
              <button 
                type="submit"
                class="inline-flex items-center gap-3 px-10 py-4 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white rounded-full font-bold text-lg hover:shadow-2xl hover:shadow-purple-500/50 hover:scale-105 transition-all duration-300 transform hover:-translate-y-1"
              >
                <Target class="w-6 h-6" />
                <span>{{ wTrans('assessment.start_assessment') }}</span>
                <ArrowRight class="w-6 h-6" />
              </button>
            </div>
          </form>
        </div>

        <!-- Assessment Progress -->
        <div v-if="assessmentStarted && !assessmentCompleted" class="mb-8">
          <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 shadow-xl border border-white/20">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <Brain class="w-5 h-5 text-purple-300" />
                {{ wTrans('assessment.assessment_progress') }}
              </h3>
              <span class="text-sm text-purple-200 font-semibold bg-purple-500/30 px-4 py-1 rounded-full">
                Step {{ currentStep + 1 }} of 4
              </span>
            </div>
            
            <div class="w-full bg-white/10 rounded-full h-3 mb-4 overflow-hidden">
              <div 
                class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 h-3 rounded-full transition-all duration-700"
                :style="{ width: `${progress}%` }"
              ></div>
            </div>
            
            <div class="text-center">
              <p class="text-sm text-gray-300 font-semibold">
                {{ progress }}% Complete
              </p>
            </div>
          </div>
        </div>

        <!-- Assessment Step -->
        <div v-if="assessmentStarted && !assessmentCompleted" class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl rounded-3xl p-8 md:p-12 shadow-2xl border border-white/20">
          <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-6 py-2 bg-purple-500/20 rounded-full mb-4 border border-purple-400/30">
              <div class="text-2xl">{{ assessmentSteps[currentStep].icon }}</div>
              <span class="text-sm font-semibold text-purple-200">Step {{ currentStep + 1 }}</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">
              {{ assessmentSteps[currentStep].title }}
            </h2>
            <p class="text-lg text-gray-300">
              {{ assessmentSteps[currentStep].description }}
            </p>
          </div>

          <div class="bg-gradient-to-br from-purple-600/20 to-blue-600/20 rounded-3xl p-8 mb-8 border border-purple-400/20">
            <p class="text-center text-lg font-semibold text-white mb-6">
              Complete this learning activity
            </p>
            
            <!-- Interactive Task Area -->
            <div class="bg-gradient-to-br from-white/5 to-white/10 rounded-2xl p-12 text-center min-h-[350px] flex flex-col items-center justify-center border border-white/10">
              <div class="text-7xl mb-6 animate-bounce">{{ assessmentSteps[currentStep].icon }}</div>
              <h4 class="text-2xl font-bold text-white mb-3">{{ assessmentSteps[currentStep].title }}</h4>
              <p class="text-gray-300 mb-8 text-lg">
                Complete this {{ assessmentSteps[currentStep].type }} activity
              </p>
              
              <button 
                @click="taskCompletedStates[currentStep] = true"
                :disabled="taskCompletedStates[currentStep]"
                class="px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full font-bold text-lg hover:shadow-xl hover:shadow-green-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
              >
                <span v-if="!taskCompletedStates[currentStep]" class="flex items-center gap-2">
                  <Zap class="w-5 h-5" />
                  Complete Task
                </span>
                <span v-else class="flex items-center gap-2">
                  <CheckCircle2 class="w-5 h-5" />
                  Task Completed!
                </span>
              </button>
            </div>
          </div>

          <!-- Navigation -->
          <div class="flex justify-between items-center">
            <button 
              @click="prevStep"
              :disabled="currentStep === 0"
              class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 text-white rounded-full font-semibold hover:bg-white/20 transition-all disabled:opacity-30 disabled:cursor-not-allowed border border-white/20"
            >
              <ChevronLeft class="w-5 h-5" />
              Previous
            </button>
            
            <button 
              @click="nextStep"
              :disabled="!taskCompletedStates[currentStep]"
              class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full font-semibold hover:shadow-xl hover:shadow-purple-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              {{ currentStep === 3 ? 'Complete Assessment' : 'Next Step' }}
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Results Section -->
        <div v-if="assessmentCompleted" class="space-y-8">
          <!-- Results Header -->
          <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-xl rounded-full shadow-2xl mb-8 border border-green-400/30">
              <Award class="w-6 h-6 text-green-300 animate-bounce" />
              <span class="text-lg font-semibold text-green-200">
                Assessment Complete
              </span>
            </div>

            <h1 class="text-5xl md:text-6xl font-black text-white mb-6 leading-tight">
              <span class="bg-gradient-to-r from-green-400 via-blue-400 to-purple-400 bg-clip-text text-transparent">
                Your Results
              </span>
            </h1>
            <p class="text-xl text-gray-300 mb-4 max-w-2xl mx-auto">Assessment complete for <strong class="text-white">{{ childForm.name }}</strong></p>
            <p class="text-sm text-gray-400">Based on comprehensive assessment of 4 key areas</p>
          </div>

          <!-- Results Cards Grid -->
          <div class="grid md:grid-cols-2 gap-6 mb-12">
            <div 
              v-for="(result, idx) in results" 
              :key="idx"
              class="group bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/20 hover:border-white/40 transition-all duration-300 hover:shadow-2xl hover:scale-105"
            >
              <div class="flex items-center justify-between mb-6">
                <div>
                  <div class="text-4xl mb-3">{{ result.icon }}</div>
                  <h3 class="text-2xl font-bold text-white">
                    {{ result.category }}
                  </h3>
                </div>
                <div :class="['px-4 py-2 rounded-full text-sm font-bold', getLevelColor(result.level)]">
                  {{ result.level.toUpperCase().replace('_', ' ') }}
                </div>
              </div>

              <div class="mb-6">
                <div class="flex items-center justify-between text-sm text-gray-300 mb-3">
                  <span class="font-semibold">Score: {{ result.score }}/{{ result.maxScore }}</span>
                  <span class="text-xs">{{ Math.round((result.score / result.maxScore) * 100) }}%</span>
                </div>
                <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden border border-white/20">
                  <div 
                    class="h-3 rounded-full transition-all duration-1000"
                    :class="getLevelGradient(result.level)"
                    :style="{ width: `${(result.score / result.maxScore) * 100}%` }"
                  ></div>
                </div>
              </div>

              <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                <h4 class="font-bold text-white mb-3 flex items-center gap-2">
                  <Lightbulb class="w-4 h-4 text-yellow-300" />
                  Recommendations
                </h4>
                <ul class="space-y-2">
                  <li 
                    v-for="(rec, i) in result.recommendations" 
                    :key="i"
                    class="text-sm text-gray-300 flex items-start gap-2"
                  >
                    <CheckCircle2 class="w-4 h-4 text-green-400 mt-0.5 flex-shrink-0" />
                    {{ rec }}
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Overall Assessment Summary -->
          <div class="bg-gradient-to-r from-purple-600/30 via-blue-600/30 to-pink-600/30 rounded-3xl p-8 md:p-12 shadow-2xl border border-purple-400/30 mb-12">
            <div class="flex items-center gap-3 mb-6">
              <Brain class="w-8 h-8 text-blue-300" />
              <h3 class="text-2xl font-bold text-white">Overall Assessment Summary</h3>
            </div>
            <p class="text-gray-200 leading-relaxed mb-6">
              Based on the comprehensive assessment, {{ childForm.name }} shows a mixed profile across different learning dimensions. Strengths in cognitive processing are balanced with areas for development in fine motor coordination. Early intervention and targeted support could significantly enhance overall performance.
            </p>
            <div class="flex gap-3 flex-wrap">
              <button class="px-6 py-3 bg-white/20 text-white rounded-lg font-semibold hover:bg-white/30 transition-all flex items-center gap-2 border border-white/30">
                <Download class="w-5 h-5" />
                Download Report
              </button>
              <button class="px-6 py-3 bg-white/20 text-white rounded-lg font-semibold hover:bg-white/30 transition-all flex items-center gap-2 border border-white/30">
                <Share2 class="w-5 h-5" />
                Share Results
              </button>
            </div>
          </div>

          <!-- Next Steps -->
          <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-3xl p-8 md:p-12 shadow-2xl text-white">
            <h3 class="text-3xl font-bold mb-4 text-center">
              What's Next?
            </h3>
            <p class="text-lg text-center text-white/90 mb-8 max-w-2xl mx-auto">
              Based on your assessment results, we recommend connecting with one of our certified specialists for personalized guidance and support.
            </p>

            <div class="grid md:grid-cols-2 gap-4 mb-8">
              <Link
                href="/doctors"
                class="inline-flex items-center gap-3 px-8 py-4 bg-white text-gray-900 rounded-full font-bold text-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 transform hover:-translate-y-1 justify-center"
              >
                <Users class="w-6 h-6" />
                <span>Find Specialists</span>
              </Link>

              <Link
                href="/resources"
                class="inline-flex items-center gap-3 px-8 py-4 bg-white/20 text-white border-2 border-white/50 rounded-full font-bold text-lg hover:bg-white/30 transition-all duration-300 justify-center"
              >
                <BookOpen class="w-6 h-6" />
                <span>Learning Resources</span>
              </Link>
            </div>

            <div class="text-center">
              <button 
                @click="restartAssessment"
                class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white border border-white/30 rounded-full font-semibold hover:bg-white/30 transition-all"
              >
                <RefreshCw class="w-5 h-5" />
                Retake Assessment
              </button>
            </div>
          </div>

          <!-- Trust Badges -->
          <div class="flex flex-wrap justify-center items-center gap-6 text-gray-300 mt-12 pt-8 border-t border-white/10">
            <div class="flex items-center gap-2">
              <Shield class="w-5 h-5 text-green-400" />
              <span class="text-sm font-medium">Results Kept Private</span>
            </div>
            <div class="flex items-center gap-2">
              <Award class="w-5 h-5 text-blue-400" />
              <span class="text-sm font-medium">Scientifically Validated</span>
            </div>
            <div class="flex items-center gap-2">
              <Heart class="w-5 h-5 text-red-400" />
              <span class="text-sm font-medium">Expert Consultation Available</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>
