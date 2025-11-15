<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { Head, Link } from '@inertiajs/vue3'
import * as THREE from 'three'
import { wTrans } from 'laravel-vue-i18n'
import BookingModal from '@/components/BookingModal.vue'
import NavigationHeader from '@/components/NavigationHeader.vue'
import { 
  Users, 
  MapPin, 
  Calendar, 
  Heart, 
  Shield,
  Phone,
  Mail,
  ChevronRight,
  Star,
  Globe,
  Award,
  ChevronDown,
  Target
} from 'lucide-vue-next'

gsap.registerPlugin(ScrollTrigger)

/**
 * DYSGRAPHIA SUPPORT & AWARENESS PLATFORM
 * Professional support for writing difficulties across Algeria
 */

interface Doctor {
  id: number
  name: string
  title: string
  specialty: string
  bio: string | null
  photo: string | null
  city_name: string
  province_name: string
  years_experience: number
  consultation_fee: number | null
}

interface Stats {
  total_doctors: number
  total_cities: number
  total_provinces: number
  total_appointments: number
}

// Reactive state
const doctors = ref<Doctor[]>([])
const featuredDoctors = ref<Doctor[]>([])
const stats = ref<Stats>({
  total_doctors: 0,
  total_cities: 0,
  total_provinces: 0,
  total_appointments: 0
})
const loading = ref(true)
const showBookingModal = ref(false)
const selectedDoctor = ref<Doctor | null>(null)

// Three.js scene references
const scenes = ref<{
  hero?: any
  robot?: any
  about?: any
  contact?: any
  footer?: any
}>({})

/**
 * Create a 3D pen that writes as user scrolls
 */
const createScrollPenAnimation = (containerId: string, sectionName: string) => {
  const container = document.getElementById(containerId)
  if (!container) return null

  // Scene setup
  const scene = new THREE.Scene()
  
  // Camera
  const camera = new THREE.PerspectiveCamera(
    45,
    container.clientWidth / container.clientHeight,
    0.1,
    1000
  )
  camera.position.set(0, 2, 8)
  camera.lookAt(0, 0, 0)

  // Renderer
  const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true })
  renderer.setSize(container.clientWidth, container.clientHeight)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setClearColor(0x000000, 0)
  container.appendChild(renderer.domElement)

  // Lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.6)
  scene.add(ambientLight)

  const pointLight = new THREE.PointLight(0x4f46e5, 1.2, 50)
  pointLight.position.set(5, 5, 5)
  scene.add(pointLight)

  const spotLight = new THREE.SpotLight(0xffffff, 0.8)
  spotLight.position.set(-5, 10, 5)
  spotLight.castShadow = true
  scene.add(spotLight)

  // Create pen group
  const penGroup = new THREE.Group()

  // Pen body (wooden texture)
  const penBodyGeometry = new THREE.CylinderGeometry(0.08, 0.08, 2.5, 16)
  const penBodyMaterial = new THREE.MeshPhongMaterial({ 
    color: 0x8B4513,
    shininess: 30,
    specular: 0x444444
  })
  const penBody = new THREE.Mesh(penBodyGeometry, penBodyMaterial)
  penGroup.add(penBody)

  // Pen tip (graphite)
  const tipGeometry = new THREE.ConeGeometry(0.08, 0.4, 16)
  const tipMaterial = new THREE.MeshPhongMaterial({ 
    color: 0x2c2c2c,
    shininess: 50
  })
  const tip = new THREE.Mesh(tipGeometry, tipMaterial)
  tip.position.y = -1.45
  penGroup.add(tip)

  // Pen point (sharp)
  const pointGeometry = new THREE.ConeGeometry(0.02, 0.15, 8)
  const pointMaterial = new THREE.MeshPhongMaterial({ 
    color: 0x000000,
    shininess: 100
  })
  const point = new THREE.Mesh(pointGeometry, pointMaterial)
  point.position.y = -1.7
  penGroup.add(point)

  // Eraser (pink)
  const eraserGeometry = new THREE.CylinderGeometry(0.09, 0.09, 0.3, 16)
  const eraserMaterial = new THREE.MeshPhongMaterial({ 
    color: 0xffb6c1,
    shininess: 10
  })
  const eraser = new THREE.Mesh(eraserGeometry, eraserMaterial)
  eraser.position.y = 1.4
  penGroup.add(eraser)

  // Metal band
  const bandGeometry = new THREE.CylinderGeometry(0.085, 0.085, 0.1, 16)
  const bandMaterial = new THREE.MeshPhongMaterial({ 
    color: 0xC0C0C0,
    shininess: 100
  })
  const band = new THREE.Mesh(bandGeometry, bandMaterial)
  band.position.y = 1.2
  penGroup.add(band)

  // Position and rotate pen for writing angle
  penGroup.position.set(-1, 0, 0)
  penGroup.rotation.z = Math.PI / 6

  scene.add(penGroup)

  // Create writing path (cursive word)
  const writingPath = new THREE.CatmullRomCurve3([
    new THREE.Vector3(-2, -1, 0),
    new THREE.Vector3(-1.5, -0.8, 0),
    new THREE.Vector3(-1, -1, 0),
    new THREE.Vector3(-0.5, -0.7, 0),
    new THREE.Vector3(0, -1, 0),
    new THREE.Vector3(0.5, -0.8, 0),
    new THREE.Vector3(1, -1, 0),
    new THREE.Vector3(1.5, -0.6, 0),
    new THREE.Vector3(2, -1, 0),
  ])

  // Create the writing trail
  const trailPoints: THREE.Vector3[] = []
  const maxTrailPoints = 100
  const trailGeometry = new THREE.BufferGeometry()
  const positions = new Float32Array(maxTrailPoints * 3)
  trailGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3))
  
  const trailMaterial = new THREE.LineBasicMaterial({ 
    color: 0x4f46e5,
    linewidth: 3,
    transparent: true,
    opacity: 0.8
  })
  const trailLine = new THREE.Line(trailGeometry, trailMaterial)
  scene.add(trailLine)

  // Scroll-triggered animation
  let scrollProgress = 0
  
  ScrollTrigger.create({
    trigger: container,
    start: 'top bottom',
    end: 'bottom top',
    scrub: 1,
    onUpdate: (self) => {
      scrollProgress = self.progress
      
      // Move pen along the path
      const point = writingPath.getPoint(scrollProgress)
      penGroup.position.x = point.x
      penGroup.position.y = point.y
      
      // Rotate pen naturally
      const tangent = writingPath.getTangent(scrollProgress)
      const angle = Math.atan2(tangent.y, tangent.x)
      penGroup.rotation.z = angle - Math.PI / 2

      // Update writing trail
      trailPoints.push(point.clone())
      if (trailPoints.length > maxTrailPoints) {
        trailPoints.shift()
      }

      const posArray = trailGeometry.attributes.position.array as Float32Array
      for (let i = 0; i < trailPoints.length; i++) {
        posArray[i * 3] = trailPoints[i].x
        posArray[i * 3 + 1] = trailPoints[i].y
        posArray[i * 3 + 2] = trailPoints[i].z
      }
      trailGeometry.attributes.position.needsUpdate = true
      trailGeometry.setDrawRange(0, trailPoints.length)
    }
  })

  // Animation loop
  let animationId = 0
  const animate = () => {
    animationId = requestAnimationFrame(animate)
    
    // Gentle floating animation
    penGroup.position.z = Math.sin(Date.now() * 0.001) * 0.3
    
    // Slight rotation for realism
    penGroup.rotation.y = Math.sin(Date.now() * 0.0005) * 0.1
    
    renderer.render(scene, camera)
  }
  animate()

  // Handle resize
  const handleResize = () => {
    if (!container) return
    camera.aspect = container.clientWidth / container.clientHeight
    camera.updateProjectionMatrix()
    renderer.setSize(container.clientWidth, container.clientHeight)
  }
  window.addEventListener('resize', handleResize)

  return { scene, camera, renderer, pen: penGroup, animationId }
}

/**
 * Create a Microsoft-style robot that follows mouse/scroll
 */
const createInteractiveRobot = (containerId: string) => {
  const container = document.getElementById(containerId)
  if (!container) return null

  // Scene setup
  const scene = new THREE.Scene()
  scene.background = null

  // Camera
  const camera = new THREE.PerspectiveCamera(
    45,
    container.clientWidth / container.clientHeight,
    0.1,
    1000
  )
  camera.position.set(0, 0, 8)
  camera.lookAt(0, 0, 0)

  // Renderer
  const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true })
  renderer.setSize(container.clientWidth, container.clientHeight)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setClearColor(0x000000, 0)
  container.appendChild(renderer.domElement)

  // Lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.8)
  scene.add(ambientLight)

  const directionalLight = new THREE.DirectionalLight(0xffffff, 1)
  directionalLight.position.set(5, 5, 5)
  scene.add(directionalLight)

  const pointLight = new THREE.PointLight(0x4f46e5, 0.5, 50)
  pointLight.position.set(-5, 5, 5)
  scene.add(pointLight)

  // Create robot group
  const robotGroup = new THREE.Group()

  // Robot body (main cube)
  const bodyGeometry = new THREE.BoxGeometry(1.5, 2, 1)
  const bodyMaterial = new THREE.MeshPhongMaterial({ 
    color: 0x4f46e5,
    shininess: 100,
    specular: 0x222222
  })
  const body = new THREE.Mesh(bodyGeometry, bodyMaterial)
  robotGroup.add(body)

  // Robot head (smaller cube)
  const headGeometry = new THREE.BoxGeometry(1, 1, 1)
  const headMaterial = new THREE.MeshPhongMaterial({ 
    color: 0x6366f1,
    shininess: 100
  })
  const head = new THREE.Mesh(headGeometry, headMaterial)
  head.position.y = 1.8
  robotGroup.add(head)

  // Eyes (small spheres)
  const eyeGeometry = new THREE.SphereGeometry(0.15, 16, 16)
  const eyeMaterial = new THREE.MeshPhongMaterial({ 
    color: 0xffffff,
    shininess: 200
  })

  const leftEye = new THREE.Mesh(eyeGeometry, eyeMaterial)
  leftEye.position.set(-0.3, 1.9, 0.4)
  robotGroup.add(leftEye)

  const rightEye = new THREE.Mesh(eyeGeometry, eyeMaterial)
  rightEye.position.set(0.3, 1.9, 0.4)
  robotGroup.add(rightEye)

  // Pupils (black spheres)
  const pupilGeometry = new THREE.SphereGeometry(0.08, 8, 8)
  const pupilMaterial = new THREE.MeshPhongMaterial({ color: 0x000000 })

  const leftPupil = new THREE.Mesh(pupilGeometry, pupilMaterial)
  leftPupil.position.set(-0.3, 1.9, 0.45)
  robotGroup.add(leftPupil)

  const rightPupil = new THREE.Mesh(pupilGeometry, pupilMaterial)
  rightPupil.position.set(0.3, 1.9, 0.45)
  robotGroup.add(rightPupil)

  // Mouth (small rectangle)
  const mouthGeometry = new THREE.BoxGeometry(0.3, 0.1, 0.05)
  const mouthMaterial = new THREE.MeshPhongMaterial({ color: 0x333333 })
  const mouth = new THREE.Mesh(mouthGeometry, mouthMaterial)
  mouth.position.set(0, 1.6, 0.45)
  robotGroup.add(mouth)

  // Arms
  const armGeometry = new THREE.CylinderGeometry(0.15, 0.15, 1.5, 16)
  const armMaterial = new THREE.MeshPhongMaterial({ 
    color: 0x7c3aed,
    shininess: 50
  })

  const leftArm = new THREE.Mesh(armGeometry, armMaterial)
  leftArm.position.set(-1, 0.2, 0)
  leftArm.rotation.z = Math.PI / 6
  robotGroup.add(leftArm)

  const rightArm = new THREE.Mesh(armGeometry, armMaterial)
  rightArm.position.set(1, 0.2, 0)
  rightArm.rotation.z = -Math.PI / 6
  robotGroup.add(rightArm)

  // Legs
  const legGeometry = new THREE.CylinderGeometry(0.2, 0.2, 1.5, 16)
  const legMaterial = new THREE.MeshPhongMaterial({ 
    color: 0x4f46e5,
    shininess: 50
  })

  const leftLeg = new THREE.Mesh(legGeometry, legMaterial)
  leftLeg.position.set(-0.4, -1.8, 0)
  robotGroup.add(leftLeg)

  const rightLeg = new THREE.Mesh(legGeometry, legMaterial)
  rightLeg.position.set(0.4, -1.8, 0)
  robotGroup.add(rightLeg)

  // Antenna
  const antennaGeometry = new THREE.CylinderGeometry(0.02, 0.02, 0.8, 8)
  const antennaMaterial = new THREE.MeshPhongMaterial({ color: 0xff0000 })
  const antenna = new THREE.Mesh(antennaGeometry, antennaMaterial)
  antenna.position.set(0, 2.4, 0)
  robotGroup.add(antenna)

  // Antenna ball
  const antennaBallGeometry = new THREE.SphereGeometry(0.08, 8, 8)
  const antennaBallMaterial = new THREE.MeshPhongMaterial({ color: 0xff0000 })
  const antennaBall = new THREE.Mesh(antennaBallGeometry, antennaBallMaterial)
  antennaBall.position.set(0, 2.8, 0)
  robotGroup.add(antennaBall)

  // Position robot
  robotGroup.position.set(3, 0, 0)
  scene.add(robotGroup)

  // Mouse tracking
  const mouse = { x: 0, y: 0 }
  let targetRotationY = 0
  let targetRotationX = 0

  const handleMouseMove = (event: MouseEvent) => {
    const rect = container.getBoundingClientRect()
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1

    // Calculate rotation based on mouse position
    targetRotationY = mouse.x * 0.5
    targetRotationX = mouse.y * 0.3
  }

  // Scroll-based movement
  let scrollY = 0
  const handleScroll = () => {
    scrollY = window.scrollY * 0.001
  }

  window.addEventListener('mousemove', handleMouseMove)
  window.addEventListener('scroll', handleScroll)

  // Handle resize
  const handleResize = () => {
    if (!container) return
    camera.aspect = container.clientWidth / container.clientHeight
    camera.updateProjectionMatrix()
    renderer.setSize(container.clientWidth, container.clientHeight)
  }
  window.addEventListener('resize', handleResize)

  // Animation loop
  let animationId = 0
  const animate = () => {
    animationId = requestAnimationFrame(animate)

    // Smooth rotation towards mouse
    robotGroup.rotation.y += (targetRotationY - robotGroup.rotation.y) * 0.05
    robotGroup.rotation.x += (targetRotationX - robotGroup.rotation.x) * 0.05

    // Gentle floating animation
    robotGroup.position.y = Math.sin(Date.now() * 0.002) * 0.2

    // Subtle breathing effect
    const scale = 1 + Math.sin(Date.now() * 0.003) * 0.02
    robotGroup.scale.setScalar(scale)

    // Antenna light pulsing
    const intensity = 0.5 + Math.sin(Date.now() * 0.005) * 0.3
    ;(antennaBall.material as THREE.MeshPhongMaterial).emissive.setHex(0xff0000 * intensity)

    // Eye blinking
    if (Math.random() < 0.005) {
      leftEye.scale.y = 0.1
      rightEye.scale.y = 0.1
      setTimeout(() => {
        leftEye.scale.y = 1
        rightEye.scale.y = 1
      }, 150)
    }

    renderer.render(scene, camera)
  }
  animate()

  return { scene, camera, renderer, robot: robotGroup, animationId, mouse }
}
const fetchDoctors = async () => {
  try {
    const response = await fetch('/api/doctors/public')
    const data = await response.json()
    doctors.value = data.doctors || []
    featuredDoctors.value = (data.doctors || []).slice(0, 3)
  } catch (error) {
    console.error('Failed to fetch doctors:', error)
  }
}

/**
 * Fetch statistics from API
 */
const fetchStats = async () => {
  try {
    const response = await fetch('/api/stats/public')
    const data = await response.json()
    stats.value = data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  } finally {
    loading.value = false
  }
}

/**
 * Animated counter for statistics
 */
const animateCounter = (
  elementId: string,
  target: number,
  duration: number = 2000
) => {
  const element = document.getElementById(elementId)
  if (!element) return

  let current = 0
  const increment = target / (duration / 16)
  
  const timer = setInterval(() => {
    current += increment
    if (current >= target) {
      current = target
      clearInterval(timer)
    }
    element.textContent = Math.floor(current).toLocaleString()
  }, 16)
}

const openBookingModal = (doctor: Doctor) => {
  selectedDoctor.value = doctor
  showBookingModal.value = true
}

const closeBookingModal = () => {
  showBookingModal.value = false
  selectedDoctor.value = null
}

const handleBooking = (bookingData: any) => {
  // User is authenticated, redirect to booking page with doctor and date/time info
  const query = new URLSearchParams({
    doctor: bookingData.doctor_id,
    date: bookingData.date,
    time: bookingData.start_time
  })
  window.location.href = `/book?${query.toString()}`
}

onMounted(async () => {
  await Promise.all([fetchDoctors(), fetchStats()])

  // Initialize GSAP animations
  
  // Hero headline word reveal
  const heroHeadline = document.querySelector('.hero-headline')
  if (heroHeadline) {
    const text = heroHeadline.textContent || ''
    const words = text.split(' ')
    
    heroHeadline.innerHTML = words
      .map(word => `<span class="word-reveal"><span class="word-inner">${word}</span></span>`)
      .join(' ')

    const wordInners = heroHeadline.querySelectorAll('.word-inner')
    
    gsap.from(wordInners, {
      duration: 1.2,
      opacity: 0,
      y: 30,
      filter: 'blur(20px)',
      stagger: {
        amount: 0.8,
        ease: 'power2.out'
      },
      ease: 'power3.out',
      delay: 0.5
    })
  }

  // Scroll-triggered text reveals
  gsap.utils.toArray('.reveal-text').forEach((element: any) => {
    gsap.from(element, {
      scrollTrigger: {
        trigger: element,
        start: 'top 85%',
        toggleActions: 'play none none reverse'
      },
      opacity: 0,
      y: 50,
      duration: 1,
      ease: 'power2.out'
    })
  })

  // Character-by-character reveals
  gsap.utils.toArray('.split-reveal').forEach((element: any) => {
    const text = element.textContent || ''
    const chars = text.split('')
    
    element.innerHTML = chars
      .map((char: string) => `<span class="char-reveal">${char === ' ' ? '&nbsp;' : char}</span>`)
      .join('')

    const charSpans = element.querySelectorAll('.char-reveal')

    gsap.from(charSpans, {
      scrollTrigger: {
        trigger: element,
        start: 'top 80%',
        end: 'bottom 60%',
        scrub: 1
      },
      opacity: 0,
      filter: 'blur(10px)',
      stagger: 0.02,
      duration: 0.5
    })
  })

  // Fade and scale animations
  gsap.utils.toArray('.fade-scale').forEach((element: any) => {
    gsap.from(element, {
      scrollTrigger: {
        trigger: element,
        start: 'top 85%',
        toggleActions: 'play none none reverse'
      },
      opacity: 0,
      scale: 0.9,
      duration: 1.2,
      ease: 'power2.out'
    })
  })

  // Parallax effects
  gsap.utils.toArray('[data-speed]').forEach((element: any) => {
    const speed = parseFloat(element.getAttribute('data-speed') || '1')
    const lag = parseFloat(element.getAttribute('data-lag') || '0')
    
    gsap.to(element, {
      scrollTrigger: {
        trigger: element,
        start: 'top bottom',
        end: 'bottom top',
        scrub: lag > 0 ? lag : true
      },
      y: (i, target) => {
        return -100 * (1 - speed)
      },
      ease: 'none'
    })
  })

  // Stagger animations
  gsap.utils.toArray('.stagger-item').forEach((element: any, index: number) => {
    gsap.from(element, {
      scrollTrigger: {
        trigger: element,
        start: 'top 90%',
        toggleActions: 'play none none reverse'
      },
      opacity: 0,
      y: 30,
      duration: 0.8,
      delay: index * 0.1,
      ease: 'power2.out'
    })
  })

  // Initialize 3D pen animations for all sections
  scenes.value.hero = createScrollPenAnimation('pen-hero', 'hero')
  scenes.value.robot = createInteractiveRobot('robot-hero')
  scenes.value.about = createScrollPenAnimation('pen-about', 'about')
  scenes.value.contact = createScrollPenAnimation('pen-contact', 'contact')
  scenes.value.footer = createScrollPenAnimation('pen-footer', 'footer')

  // Initialize ScrollSmoother (if available)
  if (typeof window !== 'undefined') {
    const smootherPlugin = (gsap as any).ScrollSmoother || (window as any).ScrollSmoother
    
    if (smootherPlugin && !smootherPlugin.get?.()) {
      try {
        smootherPlugin.create({
          wrapper: '#smooth-wrapper',
          content: '#smooth-content',
          smooth: 1.5,
          effects: true,
          smoothTouch: 0.1
        })
      } catch (e) {
        console.warn('ScrollSmoother initialization skipped:', e)
      }
    }
  }

  // Animate counters
  setTimeout(() => {
    animateCounter('doctor-count', stats.value.total_doctors)
    animateCounter('city-count', stats.value.total_cities)
    animateCounter('province-count', stats.value.total_provinces)
    animateCounter('appointment-count', stats.value.total_appointments)
  }, 500)
})

onUnmounted(() => {
  // Cleanup Three.js scenes
  Object.values(scenes.value).forEach(sceneData => {
    if (sceneData) {
      cancelAnimationFrame(sceneData.animationId)
      sceneData.renderer.dispose()
      if (sceneData.renderer.domElement.parentNode) {
        sceneData.renderer.domElement.parentNode.removeChild(sceneData.renderer.domElement)
      }
    }
  })

  // Kill all ScrollTriggers
  ScrollTrigger.getAll().forEach(trigger => trigger.kill())
})
</script>

<template>
  <Head :title="wTrans('welcome.page_title')" />

  <div id="smooth-wrapper">
    <div id="smooth-content">
      <div class="min-h-screen bg-gradient-to-b from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        
        <!-- Navigation -->
        <NavigationHeader />

        <!-- Hero Section -->
        <!-- Hero Section -->
        <section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-rose-50 via-white to-amber-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
          
          <!-- Enhanced Background Elements -->
          <div class="absolute inset-0">
            <!-- Large gradient orbs -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-gradient-to-r from-rose-200/30 to-pink-200/30 dark:from-rose-800/20 dark:to-pink-800/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-gradient-to-r from-amber-200/30 to-orange-200/30 dark:from-amber-800/20 dark:to-orange-800/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-r from-emerald-200/20 to-teal-200/20 dark:from-emerald-800/15 dark:to-teal-800/15 rounded-full blur-2xl animate-pulse" style="animation-delay: 4s"></div>
            
            <!-- Floating geometric shapes -->
            <div class="absolute top-20 left-20 w-4 h-4 bg-rose-400/40 dark:bg-rose-600/40 rotate-45 animate-bounce" style="animation-delay: 1s"></div>
            <div class="absolute top-32 right-32 w-6 h-6 bg-amber-400/40 dark:bg-amber-600/40 rounded-full animate-bounce" style="animation-delay: 3s"></div>
            <div class="absolute bottom-32 left-32 w-3 h-3 bg-emerald-400/40 dark:bg-emerald-600/40 rotate-45 animate-bounce" style="animation-delay: 5s"></div>
            <div class="absolute bottom-20 right-20 w-5 h-5 bg-blue-400/40 dark:bg-blue-600/40 rounded-full animate-bounce" style="animation-delay: 2s"></div>
            
            <!-- Additional floating elements -->
            <div class="absolute top-1/3 right-1/6 w-2 h-2 bg-purple-400/50 dark:bg-purple-600/50 rounded-full animate-ping" style="animation-delay: 1.5s"></div>
            <div class="absolute bottom-1/3 left-1/6 w-3 h-3 bg-indigo-400/50 dark:bg-indigo-600/50 rotate-45 animate-ping" style="animation-delay: 4.5s"></div>
          </div>

          <!-- Hero Content -->
          <div class="relative z-10 container mx-auto px-4 text-center" data-speed="0.95" data-lag="0.18">
            <div class="max-w-6xl mx-auto">
              
              <!-- Badge -->
              <div class="inline-flex items-center gap-2 px-6 py-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full shadow-lg mb-16 fade-scale border border-rose-200/50 dark:border-rose-800/50">
                <span class="text-lg font-medium text-gray-700 dark:text-gray-300">
                  {{ wTrans('welcome.hero_badge') }}
                </span>
              </div>

              <h1 class="hero-headline text-6xl md:text-8xl lg:text-9xl xl:text-[12rem] font-black text-gray-900 dark:text-white mb-12 leading-none tracking-tight">
                {{ wTrans('welcome.hero_headline') }}
              </h1>
              
              <p class="text-xl md:text-2xl lg:text-3xl text-gray-600 dark:text-gray-400 mb-16 reveal-text max-w-4xl mx-auto leading-relaxed font-light">
                {{ wTrans('welcome.hero_description') }}
              </p>

              <!-- Enhanced CTA Buttons -->
              <div class="flex flex-col sm:flex-row gap-6 justify-center mb-20">
                <Link 
                  href="/appointments"
                  class="group px-10 py-5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full font-bold text-xl hover:shadow-2xl hover:scale-110 transition-all duration-500 flex items-center justify-center gap-3 transform hover:-translate-y-2"
                >
                  <span>{{ wTrans('welcome.hero_cta_primary') }}</span>
                  <ChevronRight class="w-6 h-6 group-hover:translate-x-2 transition-transform" />
                </Link>
                
                <Link 
                  href="/doctors"
                  class="group px-10 py-5 bg-white/90 dark:bg-gray-800/90 text-gray-900 dark:text-white rounded-full font-bold text-xl hover:shadow-2xl hover:scale-110 transition-all duration-500 border-2 border-gray-200 dark:border-gray-700 flex items-center justify-center gap-3 backdrop-blur-sm transform hover:-translate-y-2"
                >
                  <span>{{ wTrans('welcome.hero_cta_secondary') }}</span>
                  <ChevronRight class="w-6 h-6 group-hover:translate-x-2 transition-transform" />
                </Link>
              </div>

              <!-- Enhanced Quick Stats -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-20">
                <div class="stagger-item bg-white/70 dark:bg-gray-800/70 backdrop-blur-md rounded-3xl p-8 border border-rose-100 dark:border-gray-700 hover:border-rose-200 dark:hover:border-gray-600 transition-all duration-300 hover:scale-105 hover:shadow-xl">
                  <div class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white" id="doctor-count">0</div>
                  <div class="text-lg text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ wTrans('welcome.specialists_count') }}</div>
                </div>
                <div class="stagger-item bg-white/70 dark:bg-gray-800/70 backdrop-blur-md rounded-3xl p-8 border border-amber-100 dark:border-gray-700 hover:border-amber-200 dark:hover:border-gray-600 transition-all duration-300 hover:scale-105 hover:shadow-xl">
                  <div class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white" id="city-count">0</div>
                  <div class="text-lg text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ wTrans('welcome.cities_count') }}</div>
                </div>
                <div class="stagger-item bg-white/70 dark:bg-gray-800/70 backdrop-blur-md rounded-3xl p-8 border border-emerald-100 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-gray-600 transition-all duration-300 hover:scale-105 hover:shadow-xl">
                  <div class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white" id="province-count">0</div>
                  <div class="text-lg text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ wTrans('welcome.provinces_count') }}</div>
                </div>
                <div class="stagger-item bg-white/70 dark:bg-gray-800/70 backdrop-blur-md rounded-3xl p-8 border border-blue-100 dark:border-gray-700 hover:border-blue-200 dark:hover:border-gray-600 transition-all duration-300 hover:scale-105 hover:shadow-xl">
                  <div class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white" id="appointment-count">0</div>
                  <div class="text-lg text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ wTrans('welcome.appointments_count') }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Scroll indicator -->
          <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <ChevronDown class="w-8 h-8 text-rose-400" />
          </div>
        </section>

        <!-- Activity Games Section -->
        <section class="relative py-32 bg-gradient-to-b from-white to-indigo-50 dark:from-gray-800 dark:to-indigo-900/20 overflow-hidden">
          <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center mb-16">
              <h2 class="split-reveal text-4xl md:text-6xl font-light text-gray-900 dark:text-white mb-6">
                🎮 {{ wTrans('activities.title') }}
              </h2>
              <p class="text-xl text-gray-600 dark:text-gray-400 reveal-text">
                {{ wTrans('activities.description') || 'Fun and engaging games to support dysgraphia assessment and learning' }}
              </p>
            </div>

            <!-- Activity Games Preview Cards -->
            <div class="grid md:grid-cols-3 gap-8 mb-12">
              <div class="stagger-item bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-3xl p-8 hover:shadow-2xl transition-all">
                <div class="text-5xl mb-4">✏️</div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ wTrans('activities.emoji_choice') }}</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ wTrans('activities.click_emoji') }}</p>
              </div>

              <div class="stagger-item bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-3xl p-8 hover:shadow-2xl transition-all">
                <div class="text-5xl mb-4">⌨️</div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ wTrans('activities.text_copy_timed') }}</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ wTrans('activities.type_text') }}</p>
              </div>

              <div class="stagger-item bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-3xl p-8 hover:shadow-2xl transition-all">
                <div class="text-5xl mb-4">🎨</div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ wTrans('activities.shape_copy_canvas') }}</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ wTrans('activities.draw_shape') }}</p>
              </div>
            </div>

            <!-- View All Activities Button -->
            <div class="text-center fade-scale">
              <Link 
                href="/activities"
                class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full font-bold text-xl hover:shadow-2xl hover:scale-110 transition-all duration-500 transform hover:-translate-y-2"
              >
                <span>{{ wTrans('activities.start_playing') }}</span>
                <ChevronRight class="w-6 h-6" />
              </Link>
            </div>
          </div>
        </section>

        <!-- Featured Doctors Section -->
        <section class="relative py-32 bg-gradient-to-b from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 overflow-hidden">
          <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center mb-16">
              <h2 class="split-reveal text-4xl md:text-6xl font-light text-gray-900 dark:text-white mb-6">
                {{ wTrans('welcome.featured_specialists') }}
              </h2>
              <p class="text-xl text-gray-600 dark:text-gray-400 reveal-text">
                {{ wTrans('welcome.meet_our_specialists') }}
              </p>
            </div>

            <!-- Featured Doctors Grid -->
            <div v-if="!loading && featuredDoctors.length > 0" class="grid md:grid-cols-3 gap-8 mb-12">
              <div 
                v-for="doctor in featuredDoctors" 
                :key="doctor.id"
                class="stagger-item bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group border border-gray-100 dark:border-gray-700"
              >
                <div class="aspect-square overflow-hidden">
                  <img 
                    :src="doctor.photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(doctor.name)}&size=400&background=4f46e5&color=fff`"
                    :alt="doctor.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                  />
                </div>
                <div class="p-6">
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ doctor.title }} {{ doctor.name }}
                  </h3>
                  <div class="flex items-center gap-2 text-indigo-600 mb-3">
                    <Award class="w-4 h-4" />
                    <span class="text-sm">{{ doctor.specialty }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 mb-3">
                    <MapPin class="w-4 h-4" />
                    <span class="text-sm">{{ doctor.city_name }}, {{ doctor.province_name }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 mb-4">
                    <Star class="w-4 h-4 text-yellow-500" />
                    <span class="text-sm">{{ doctor.years_experience }} {{ wTrans('welcome.years_experience') }}</span>
                  </div>
                  <div class="flex gap-2">
                    <Link 
                      :href="`/doctors/${doctor.id}`"
                      class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center rounded-lg font-semibold hover:shadow-lg transition-all"
                    >
                      {{ wTrans('welcome.view_profile') }}
                    </Link>
                    <button 
                      @click="openBookingModal(doctor)"
                      class="px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                      <Calendar class="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- View All Doctors Button -->
            <div class="text-center fade-scale">
              <Link 
                href="/doctors"
                class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full font-bold text-xl hover:shadow-2xl hover:scale-110 transition-all duration-500 transform hover:-translate-y-2"
              >
                <span>{{ wTrans('welcome.view_all_specialists') }}</span>
                <ChevronRight class="w-6 h-6" />
              </Link>
            </div>
          </div>
        </section>

        <section id="about" class="content-section relative py-32 bg-white dark:bg-gray-800 overflow-hidden">
          
          <!-- 3D Pen Animation Container -->
          <div id="pen-about" class="absolute top-0 right-0 w-full h-full pointer-events-none opacity-20 dark:opacity-30"></div>

          <!-- Parallax decorative elements -->
          <div class="absolute top-10 left-10 w-32 h-32 bg-rose-100 dark:bg-rose-900/20 rounded-full blur-2xl" data-speed="0.7"></div>
          <div class="absolute bottom-10 right-10 w-40 h-40 bg-emerald-100 dark:bg-emerald-900/20 rounded-full blur-2xl" data-speed="0.5"></div>
          
          <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center mb-16">
              <h2 class="split-reveal text-4xl md:text-6xl font-light text-gray-900 dark:text-white mb-6">
                {{ $t('about.understanding_title') }}
              </h2>
              <p class="text-xl text-gray-600 dark:text-gray-400 reveal-text">
                {{ $t('about.understanding_description') }}
              </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-16">
              <div class="stagger-item bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-3xl p-8 hover:shadow-2xl transition-shadow">
                <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center mb-6">
                  <BookOpen class="w-8 h-8 text-white" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ $t('about.what_is_title') }}</h3>
                <p class="text-gray-600 dark:text-gray-400 reveal-text">
                  {{ $t('about.what_is_description') }}
                </p>
              </div>

              <div class="stagger-item bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-3xl p-8 hover:shadow-2xl transition-shadow">
                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mb-6">
                  <Heart class="w-8 h-8 text-white" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ $t('about.signs_title') }}</h3>
                <p class="text-gray-600 dark:text-gray-400 reveal-text">
                  {{ $t('about.signs_description') }}
                </p>
              </div>

              <div class="stagger-item bg-gradient-to-br from-pink-50 to-indigo-50 dark:from-pink-900/20 dark:to-indigo-900/20 rounded-3xl p-8 hover:shadow-2xl transition-shadow">
                <div class="w-16 h-16 bg-pink-600 rounded-full flex items-center justify-center mb-6">
                  <Shield class="w-8 h-8 text-white" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ $t('about.how_we_help_title') }}</h3>
                <p class="text-gray-600 dark:text-gray-400 reveal-text">
                  {{ $t('about.how_we_help_description') }}
                </p>
              </div>
            </div>

            <!-- Image showcase -->
            <div class="grid md:grid-cols-2 gap-8">
              <div class="rounded-2xl overflow-hidden shadow-2xl fade-scale" data-speed="0.85" data-lag="0.2">
                <img 
                  src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&h=400&fit=crop" 
                  alt="Child practicing writing"
                  class="w-full h-full object-cover hover:scale-110 transition-transform duration-700"
                />
              </div>
              <div class="rounded-2xl overflow-hidden shadow-2xl fade-scale" data-speed="0.8" data-lag="0.2">
                <img 
                  src="https://images.unsplash.com/photo-1516534775068-ba3e7458af70?w=600&h=400&fit=crop" 
                  alt="Therapy session"
                  class="w-full h-full object-cover hover:scale-110 transition-transform duration-700"
                />
              </div>
            </div>
          </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="content-section relative py-32 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 overflow-hidden">
          
          <!-- 3D Pen Animation Container -->
          <div id="pen-contact" class="absolute inset-0 pointer-events-none opacity-20 dark:opacity-30"></div>

          <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto">
              <div class="text-center mb-12">
                <h2 class="split-reveal text-4xl md:text-6xl font-light text-gray-900 dark:text-white mb-6">
                  {{ $t('contact.get_in_touch') }}
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 reveal-text">
                  {{ $t('contact.contact_description') }}
                </p>
              </div>

              <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-6">
                  <div class="flex items-start gap-4 fade-scale">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center flex-shrink-0">
                      <Phone class="w-6 h-6 text-indigo-600" />
                    </div>
                    <div>
                      <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $t('contact.phone') }}</h3>
                      <p class="text-gray-600 dark:text-gray-400">{{ $t('contact.phone_number') }}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-4 fade-scale">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center flex-shrink-0">
                      <Mail class="w-6 h-6 text-purple-600" />
                    </div>
                    <div>
                      <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $t('contact.email') }}</h3>
                      <p class="text-gray-600 dark:text-gray-400">{{ $t('contact.email_address') }}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-4 fade-scale">
                    <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center flex-shrink-0">
                      <MapPin class="w-6 h-6 text-pink-600" />
                    </div>
                    <div>
                      <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $t('contact.locations') }}</h3>
                      <p class="text-gray-600 dark:text-gray-400">{{ $t('contact.locations_description') }}</p>
                    </div>
                  </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-3xl p-8 fade-scale">
                  <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ $t('contact.quick_contact') }}
                  </h3>
                  <form class="space-y-4">
                    <input 
                      type="text" 
                      :placeholder="$t('contact.name_placeholder')"
                      class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-indigo-500 transition-colors"
                    />
                    <input 
                      type="email" 
                      :placeholder="$t('contact.email_placeholder')"
                      class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-indigo-500 transition-colors"
                    />
                    <textarea 
                      :placeholder="$t('contact.message_placeholder')"
                      rows="4"
                      class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-indigo-500 transition-colors"
                    ></textarea>
                    <button 
                      type="submit"
                      class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                    >
                      {{ $t('contact.send_message') }}
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Footer -->
        <footer class="relative bg-gray-900 text-white py-16 overflow-hidden">
          
          <!-- 3D Pen Animation Container -->
          <div id="pen-footer" class="absolute inset-0 pointer-events-none opacity-15"></div>

          <div class="container mx-auto px-4 relative z-10">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
              <div class="fade-scale">
                <div class="flex items-center gap-2 mb-4">
                  <BookOpen class="w-6 h-6 text-indigo-400" />
                  <span class="text-xl font-bold">{{ $t('welcome.page_title') }}</span>
                </div>
                <p class="text-gray-400">
                  {{ $t('welcome.page_description') }}
                </p>
              </div>

              <div class="fade-scale">
                <h4 class="font-bold mb-4">{{ $t('footer.quick_links') }}</h4>
                <ul class="space-y-2 text-gray-400">
                  <li><a href="#home" class="hover:text-white transition-colors">{{ $t('sidebar.main') }}</a></li>
                  <li><a href="#about" class="hover:text-white transition-colors">{{ $t('about.mission_title') }}</a></li>
                  <li><Link href="/doctors" class="hover:text-white transition-colors">{{ $t('specialists.page_title') }}</Link></li>
                  <li><Link href="/appointments" class="hover:text-white transition-colors">{{ $t('bookings.book_appointment') }}</Link></li>
                </ul>
              </div>

              <div class="fade-scale">
                <h4 class="font-bold mb-4">{{ $t('footer.resources') }}</h4>
                <ul class="space-y-2 text-gray-400">
                  <li><Link href="/about" class="hover:text-white transition-colors">{{ $t('about.page_title') }}</Link></li>
                  <li><Link href="/resources" class="hover:text-white transition-colors">{{ $t('resources.page_title') }}</Link></li>
                  <li><Link href="/faq" class="hover:text-white transition-colors">{{ $t('faq.page_title') }}</Link></li>
                  <li><Link href="/map" class="hover:text-white transition-colors">{{ $t('footer.find_specialists') }}</Link></li>
                </ul>
              </div>

              <div class="fade-scale">
                <h4 class="font-bold mb-4">{{ $t('footer.connect_with_us') }}</h4>
                <div class="flex gap-4">
                  <a href="#" class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center hover:bg-indigo-700 transition-colors">
                    <span class="sr-only">Facebook</span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                  </a>
                  <a href="#" class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center hover:bg-purple-700 transition-colors">
                    <span class="sr-only">Twitter</span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg>
                  </a>
                  <a href="#" class="w-10 h-10 bg-pink-600 rounded-full flex items-center justify-center hover:bg-pink-700 transition-colors">
                    <span class="sr-only">Instagram</span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M7.5 2h9A5.5 5.5 0 0122 7.5v9a5.5 5.5 0 01-5.5 5.5h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2z"/></svg>
                  </a>
                </div>
              </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
              <p>{{ $t('footer.copyright') }}</p>
            </div>
          </div>
        </footer>

      </div>
    </div>

    <!-- Booking Modal -->
    <BookingModal
      :is-open="showBookingModal"
      :doctor="selectedDoctor"
      @close="closeBookingModal"
      @book="handleBooking"
    />
  </div>
</template>

<style scoped>
/* Smooth scroll wrapper */
#smooth-wrapper {
  position: relative;
  width: 100%;
  overflow: hidden;
}

#smooth-content {
  min-height: 100%;
}

/* Hero headline with overflow for masking */
.hero-headline {
  overflow: hidden;
  font-size: clamp(2rem, 5vw, 4rem);
  line-height: 1.2;
}

/* Content sections */
.content-section {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Word reveal containers */
.word-reveal {
  display: inline-block;
  overflow: hidden;
  margin: 0 0.15em;
}

.word-reveal .word-inner {
  display: inline-block;
  will-change: transform, opacity, filter;
}

/* Character reveal */
.char-reveal {
  display: inline-block;
  will-change: transform, opacity, filter;
}

/* Reveal text with clip-path */
.reveal-text {
  clip-path: inset(0 0 0 0);
  will-change: transform, opacity;
}

/* Parallax elements */
[data-speed] {
  will-change: transform;
}

/* Split reveal text */
.split-reveal {
  overflow: hidden;
}

/* Blob animation */
@keyframes blob {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  25% {
    transform: translate(20px, -20px) scale(1.1);
  }
  50% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  75% {
    transform: translate(20px, 20px) scale(1.05);
  }
}

.animate-blob {
  animation: blob 20s ease-in-out infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

/* Bounce animation */
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-bounce {
  animation: bounce 2s ease-in-out infinite;
}

/* Smooth scroll behavior */
html {
  scroll-behavior: smooth;
}
</style>
