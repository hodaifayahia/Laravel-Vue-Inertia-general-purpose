import { onMounted, onUnmounted, shallowRef } from 'vue'
import * as THREE from 'three'

export function useThreeJsGlobe(containerId: string) {
  // Use shallowRef to avoid Vue's deep reactivity on Three.js objects
  const scene = shallowRef<THREE.Scene | null>(null)
  const camera = shallowRef<THREE.PerspectiveCamera | null>(null)
  const renderer = shallowRef<THREE.WebGLRenderer | null>(null)
  const animationId = shallowRef<number | null>(null)

  const init = () => {
    const container = document.getElementById(containerId)
    if (!container) return

    // Scene
    scene.value = new THREE.Scene()
    scene.value.background = null

    // Camera
    camera.value = new THREE.PerspectiveCamera(
      45,
      container.clientWidth / container.clientHeight,
      0.1,
      1000
    )
    camera.value.position.z = 15

    // Renderer
    renderer.value = new THREE.WebGLRenderer({ alpha: true, antialias: true })
    renderer.value.setSize(container.clientWidth, container.clientHeight)
    renderer.value.setPixelRatio(window.devicePixelRatio)
    container.appendChild(renderer.value.domElement)

    // Create globe
    createGlobe()

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6)
    scene.value.add(ambientLight)

    const pointLight1 = new THREE.PointLight(0x4f46e5, 1, 50)
    pointLight1.position.set(10, 10, 10)
    scene.value.add(pointLight1)

    const pointLight2 = new THREE.PointLight(0x9333ea, 0.8, 50)
    pointLight2.position.set(-10, -10, 10)
    scene.value.add(pointLight2)

    // Handle resize
    const handleResize = () => {
      if (!camera.value || !renderer.value || !container) return
      camera.value.aspect = container.clientWidth / container.clientHeight
      camera.value.updateProjectionMatrix()
      renderer.value.setSize(container.clientWidth, container.clientHeight)
    }
    window.addEventListener('resize', handleResize)

    animate()
  }

  const createGlobe = () => {
    if (!scene.value) return

    // Main globe
    const globeGeometry = new THREE.SphereGeometry(5, 64, 64)
    
    // Custom shader material for gradient effect
    const globeMaterial = new THREE.MeshPhongMaterial({
      color: 0x4f46e5,
      emissive: 0x1e1b4b,
      specular: 0x9333ea,
      shininess: 50,
      transparent: true,
      opacity: 0.9,
    })
    
    const globe = new THREE.Mesh(globeGeometry, globeMaterial)
    scene.value.add(globe)

    // Wireframe overlay
    const wireframeGeometry = new THREE.SphereGeometry(5.05, 32, 32)
    const wireframeMaterial = new THREE.MeshBasicMaterial({
      color: 0x6366f1,
      wireframe: true,
      transparent: true,
      opacity: 0.3,
    })
    const wireframe = new THREE.Mesh(wireframeGeometry, wireframeMaterial)
    scene.value.add(wireframe)

    // Add location pins (random positions on globe surface)
    const pinPositions = generatePinPositions(20)
    pinPositions.forEach(pos => {
      const pin = createLocationPin()
      pin.position.copy(pos)
      pin.lookAt(scene.value!.position)
      scene.value!.add(pin)
    })

    // Particles around globe
    const particlesGeometry = new THREE.BufferGeometry()
    const particlesCount = 1000
    const positions = new Float32Array(particlesCount * 3)

    for (let i = 0; i < particlesCount * 3; i++) {
      positions[i] = (Math.random() - 0.5) * 30
    }

    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3))
    
    const particlesMaterial = new THREE.PointsMaterial({
      color: 0x6366f1,
      size: 0.1,
      transparent: true,
      opacity: 0.6,
    })

    const particles = new THREE.Points(particlesGeometry, particlesMaterial)
    scene.value.add(particles)

    // Store objects for animation
    ;(scene.value as any).globe = globe
    ;(scene.value as any).wireframe = wireframe
    ;(scene.value as any).particles = particles
  }

  const generatePinPositions = (count: number): THREE.Vector3[] => {
    const positions: THREE.Vector3[] = []
    const radius = 5.1

    for (let i = 0; i < count; i++) {
      const theta = Math.random() * Math.PI * 2
      const phi = Math.acos(2 * Math.random() - 1)

      const x = radius * Math.sin(phi) * Math.cos(theta)
      const y = radius * Math.sin(phi) * Math.sin(theta)
      const z = radius * Math.cos(phi)

      positions.push(new THREE.Vector3(x, y, z))
    }

    return positions
  }

  const createLocationPin = (): THREE.Group => {
    const pin = new THREE.Group()

    // Pin body
    const bodyGeometry = new THREE.SphereGeometry(0.15, 16, 16)
    const bodyMaterial = new THREE.MeshPhongMaterial({
      color: 0xec4899,
      emissive: 0xbe185d,
    })
    const body = new THREE.Mesh(bodyGeometry, bodyMaterial)
    pin.add(body)

    // Pin glow
    const glowGeometry = new THREE.SphereGeometry(0.2, 16, 16)
    const glowMaterial = new THREE.MeshBasicMaterial({
      color: 0xfbbf24,
      transparent: true,
      opacity: 0.4,
    })
    const glow = new THREE.Mesh(glowGeometry, glowMaterial)
    pin.add(glow)

    return pin
  }

  const animate = () => {
    if (!scene.value || !camera.value || !renderer.value) return

    animationId.value = requestAnimationFrame(animate)

    const globe = (scene.value as any).globe
    const wireframe = (scene.value as any).wireframe
    const particles = (scene.value as any).particles

    // Rotate globe
    if (globe) {
      globe.rotation.y += 0.002
    }

    // Counter-rotate wireframe
    if (wireframe) {
      wireframe.rotation.y -= 0.001
      wireframe.rotation.x += 0.0005
    }

    // Rotate particles
    if (particles) {
      particles.rotation.y += 0.0005
    }

    // Gentle camera orbit
    const time = Date.now() * 0.0001
    camera.value.position.x = Math.sin(time) * 2
    camera.value.position.y = Math.cos(time * 0.7) * 2
    camera.value.lookAt(scene.value.position)

    renderer.value.render(scene.value, camera.value)
  }

  const cleanup = () => {
    if (animationId.value) {
      cancelAnimationFrame(animationId.value)
    }
    if (renderer.value) {
      renderer.value.dispose()
      const container = document.getElementById(containerId)
      if (container && renderer.value.domElement.parentNode === container) {
        container.removeChild(renderer.value.domElement)
      }
    }
  }

  onMounted(() => {
    init()
  })

  onUnmounted(() => {
    cleanup()
  })

  return {
    scene,
    camera,
    renderer,
  }
}
