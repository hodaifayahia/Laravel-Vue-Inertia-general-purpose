import { onMounted, onUnmounted, shallowRef } from 'vue'
import * as THREE from 'three'

export function useThreeJsWriting(containerId: string) {
  // Use shallowRef to avoid Vue's deep reactivity on Three.js objects
  const scene = shallowRef<THREE.Scene | null>(null)
  const camera = shallowRef<THREE.PerspectiveCamera | null>(null)
  const renderer = shallowRef<THREE.WebGLRenderer | null>(null)
  const animationId = shallowRef<number | null>(null)

  const init = () => {
    const container = document.getElementById(containerId)
    if (!container) return

    // Scene setup
    scene.value = new THREE.Scene()
    scene.value.background = null

    // Camera
    camera.value = new THREE.PerspectiveCamera(
      75,
      container.clientWidth / container.clientHeight,
      0.1,
      1000
    )
    camera.value.position.z = 5

    // Renderer
    renderer.value = new THREE.WebGLRenderer({ alpha: true, antialias: true })
    renderer.value.setSize(container.clientWidth, container.clientHeight)
    renderer.value.setPixelRatio(window.devicePixelRatio)
    container.appendChild(renderer.value.domElement)

    // Create writing hand animation
    createWritingAnimation()

    // Handle resize
    const handleResize = () => {
      if (!camera.value || !renderer.value || !container) return
      camera.value.aspect = container.clientWidth / container.clientHeight
      camera.value.updateProjectionMatrix()
      renderer.value.setSize(container.clientWidth, container.clientHeight)
    }
    window.addEventListener('resize', handleResize)

    // Animation loop
    animate()
  }

  const createWritingAnimation = () => {
    if (!scene.value) return

    // Create a pen/pencil
    const penGeometry = new THREE.CylinderGeometry(0.05, 0.05, 1.5, 8)
    const penMaterial = new THREE.MeshPhongMaterial({ color: 0x4f46e5 })
    const pen = new THREE.Mesh(penGeometry, penMaterial)
    pen.rotation.z = Math.PI / 4
    pen.position.set(-2, 0, 0)
    scene.value.add(pen)

    // Create tip (darker)
    const tipGeometry = new THREE.ConeGeometry(0.05, 0.3, 8)
    const tipMaterial = new THREE.MeshPhongMaterial({ color: 0x1e1b4b })
    const tip = new THREE.Mesh(tipGeometry, tipMaterial)
    tip.position.set(-2, -0.9, 0)
    tip.rotation.z = Math.PI / 4
    scene.value.add(tip)

    // Create writing path (letters/squiggles)
    const points: THREE.Vector3[] = []
    const curve = new THREE.CatmullRomCurve3([
      new THREE.Vector3(-1, -0.5, 0),
      new THREE.Vector3(-0.5, 0, 0),
      new THREE.Vector3(0, -0.3, 0),
      new THREE.Vector3(0.5, 0.2, 0),
      new THREE.Vector3(1, -0.1, 0),
      new THREE.Vector3(1.5, 0.3, 0),
      new THREE.Vector3(2, 0, 0),
    ])

    const pathGeometry = new THREE.BufferGeometry().setFromPoints(curve.getPoints(100))
    const pathMaterial = new THREE.LineBasicMaterial({ 
      color: 0x6366f1, 
      linewidth: 3,
      transparent: true,
      opacity: 0.8
    })
    const path = new THREE.Line(pathGeometry, pathMaterial)
    scene.value.add(path)

    // Animate pen along the path
    let progress = 0
    const animatePen = () => {
      progress += 0.003
      if (progress > 1) progress = 0

      const point = curve.getPoint(progress)
      pen.position.copy(point)
      tip.position.copy(point)
      tip.position.y -= 0.2

      // Rotate pen slightly for realism
      pen.rotation.y = Math.sin(progress * Math.PI * 2) * 0.2
      tip.rotation.y = Math.sin(progress * Math.PI * 2) * 0.2
    }

    // Store animation function
    ;(scene.value as any).customAnimate = animatePen

    // Add lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6)
    scene.value.add(ambientLight)

    const pointLight = new THREE.PointLight(0xffffff, 1, 100)
    pointLight.position.set(5, 5, 5)
    scene.value.add(pointLight)

    const spotLight = new THREE.SpotLight(0x4f46e5, 0.8)
    spotLight.position.set(-5, 5, 5)
    scene.value.add(spotLight)
  }

  const animate = () => {
    if (!scene.value || !camera.value || !renderer.value) return

    animationId.value = requestAnimationFrame(animate)

    // Run custom animation if exists
    if ((scene.value as any).customAnimate) {
      ;(scene.value as any).customAnimate()
    }

    // Gentle camera movement
    camera.value.position.x = Math.sin(Date.now() * 0.0005) * 0.5
    camera.value.position.y = Math.cos(Date.now() * 0.0003) * 0.3

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
