import { shallowRef, onMounted, onUnmounted } from 'vue'
import * as THREE from 'three'

export function useThreeJsParticles(containerId: string) {
  const scene = shallowRef<THREE.Scene | null>(null)
  const camera = shallowRef<THREE.PerspectiveCamera | null>(null)
  const renderer = shallowRef<THREE.WebGLRenderer | null>(null)
  const particles = shallowRef<THREE.Points | null>(null)
  const animationId = shallowRef<number | null>(null)

  const init = () => {
    const container = document.getElementById(containerId)
    if (!container) return

    // Scene setup
    scene.value = new THREE.Scene()

    // Camera setup
    camera.value = new THREE.PerspectiveCamera(
      75,
      container.clientWidth / container.clientHeight,
      0.1,
      1000
    )
    camera.value.position.z = 5

    // Renderer setup
    renderer.value = new THREE.WebGLRenderer({ alpha: true, antialias: true })
    renderer.value.setSize(container.clientWidth, container.clientHeight)
    renderer.value.setClearColor(0x000000, 0)
    container.appendChild(renderer.value.domElement)

    // Create particles
    const particleCount = 1000
    const positions = new Float32Array(particleCount * 3)
    const colors = new Float32Array(particleCount * 3)

    for (let i = 0; i < particleCount * 3; i += 3) {
      // Random positions in a sphere
      const radius = Math.random() * 10 + 5
      const theta = Math.random() * Math.PI * 2
      const phi = Math.random() * Math.PI

      positions[i] = radius * Math.sin(phi) * Math.cos(theta)
      positions[i + 1] = radius * Math.sin(phi) * Math.sin(theta)
      positions[i + 2] = radius * Math.cos(phi)

      // Random colors with blue/purple theme
      colors[i] = Math.random() * 0.3 + 0.2     // R
      colors[i + 1] = Math.random() * 0.3 + 0.1 // G
      colors[i + 2] = Math.random() * 0.5 + 0.5 // B
    }

    const geometry = new THREE.BufferGeometry()
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3))
    geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3))

    const material = new THREE.PointsMaterial({
      size: 0.02,
      vertexColors: true,
      transparent: true,
      opacity: 0.6,
      blending: THREE.AdditiveBlending,
    })

    particles.value = new THREE.Points(geometry, material)
    scene.value.add(particles.value)

    // Lighting
    const ambientLight = new THREE.AmbientLight(0x404040, 0.4)
    scene.value.add(ambientLight)

    const pointLight = new THREE.PointLight(0x4f46e5, 1, 100)
    pointLight.position.set(10, 10, 10)
    scene.value.add(pointLight)

    // Handle resize
    const handleResize = () => {
      if (!camera.value || !renderer.value || !container) return

      camera.value.aspect = container.clientWidth / container.clientHeight
      camera.value.updateProjectionMatrix()
      renderer.value.setSize(container.clientWidth, container.clientHeight)
    }

    window.addEventListener('resize', handleResize)

    // Animation loop
    const animate = () => {
      animationId.value = requestAnimationFrame(animate)

      if (particles.value) {
        particles.value.rotation.x += 0.001
        particles.value.rotation.y += 0.002

        // Subtle floating motion
        const time = Date.now() * 0.0005
        const positions = particles.value.geometry.attributes.position.array as Float32Array

        for (let i = 0; i < positions.length; i += 3) {
          positions[i + 1] += Math.sin(time + i * 0.01) * 0.001
        }

        particles.value.geometry.attributes.position.needsUpdate = true
      }

      if (renderer.value && scene.value && camera.value) {
        renderer.value.render(scene.value, camera.value)
      }
    }

    animate()

    // Cleanup function
    const cleanup = () => {
      if (animationId.value) {
        cancelAnimationFrame(animationId.value)
      }
      if (renderer.value && container) {
        container.removeChild(renderer.value.domElement)
        renderer.value.dispose()
      }
      window.removeEventListener('resize', handleResize)
    }

    return cleanup
  }

  onMounted(() => {
    const cleanup = init()
    if (cleanup) {
      onUnmounted(cleanup)
    }
  })

  return {
    scene,
    camera,
    renderer,
    particles,
  }
}