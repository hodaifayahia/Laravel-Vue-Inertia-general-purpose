import { onMounted, onUnmounted, shallowRef } from 'vue'
import * as THREE from 'three'

export function useThreeJsFox(containerId: string) {
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
      60,
      container.clientWidth / container.clientHeight,
      0.1,
      1000
    )
    camera.value.position.set(0, 2, 8)
    camera.value.lookAt(0, 1, 0)

    // Renderer
    renderer.value = new THREE.WebGLRenderer({ alpha: true, antialias: true })
    renderer.value.setSize(container.clientWidth, container.clientHeight)
    renderer.value.setPixelRatio(window.devicePixelRatio)
    renderer.value.shadowMap.enabled = true
    container.appendChild(renderer.value.domElement)

    // Create fox
    createFox()

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5)
    scene.value.add(ambientLight)

    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8)
    directionalLight.position.set(5, 10, 5)
    directionalLight.castShadow = true
    scene.value.add(directionalLight)

    const spotLight = new THREE.SpotLight(0xff9500, 0.5)
    spotLight.position.set(-5, 5, 0)
    scene.value.add(spotLight)

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

  const createFox = () => {
    if (!scene.value) return

    const fox = new THREE.Group()

    // Body
    const bodyGeometry = new THREE.CapsuleGeometry(0.5, 1, 4, 8)
    const bodyMaterial = new THREE.MeshPhongMaterial({ color: 0xff6b35 })
    const body = new THREE.Mesh(bodyGeometry, bodyMaterial)
    body.rotation.z = Math.PI / 2
    body.castShadow = true
    fox.add(body)

    // Head
    const headGeometry = new THREE.SphereGeometry(0.5, 16, 16)
    const headMaterial = new THREE.MeshPhongMaterial({ color: 0xff6b35 })
    const head = new THREE.Mesh(headGeometry, headMaterial)
    head.position.set(0.8, 0.3, 0)
    head.scale.set(1, 0.9, 0.9)
    head.castShadow = true
    fox.add(head)

    // Snout
    const snoutGeometry = new THREE.ConeGeometry(0.25, 0.4, 8)
    const snoutMaterial = new THREE.MeshPhongMaterial({ color: 0xff8c5a })
    const snout = new THREE.Mesh(snoutGeometry, snoutMaterial)
    snout.rotation.z = -Math.PI / 2
    snout.position.set(1.2, 0.2, 0)
    fox.add(snout)

    // Ears (triangular)
    const earGeometry = new THREE.ConeGeometry(0.2, 0.5, 4)
    const earMaterial = new THREE.MeshPhongMaterial({ color: 0xff6b35 })
    
    const leftEar = new THREE.Mesh(earGeometry, earMaterial)
    leftEar.position.set(0.8, 0.7, 0.2)
    leftEar.rotation.z = 0.2
    fox.add(leftEar)

    const rightEar = new THREE.Mesh(earGeometry, earMaterial)
    rightEar.position.set(0.8, 0.7, -0.2)
    rightEar.rotation.z = 0.2
    fox.add(rightEar)

    // Eyes
    const eyeGeometry = new THREE.SphereGeometry(0.08, 8, 8)
    const eyeMaterial = new THREE.MeshPhongMaterial({ color: 0x000000 })
    
    const leftEye = new THREE.Mesh(eyeGeometry, eyeMaterial)
    leftEye.position.set(1.1, 0.4, 0.15)
    fox.add(leftEye)

    const rightEye = new THREE.Mesh(eyeGeometry, eyeMaterial)
    rightEye.position.set(1.1, 0.4, -0.15)
    fox.add(rightEye)

    // Nose
    const noseGeometry = new THREE.SphereGeometry(0.06, 8, 8)
    const noseMaterial = new THREE.MeshPhongMaterial({ color: 0x000000 })
    const nose = new THREE.Mesh(noseGeometry, noseMaterial)
    nose.position.set(1.4, 0.2, 0)
    fox.add(nose)

    // Tail
    const tailGeometry = new THREE.ConeGeometry(0.3, 1.2, 8)
    const tailMaterial = new THREE.MeshPhongMaterial({ color: 0xff6b35 })
    const tail = new THREE.Mesh(tailGeometry, tailMaterial)
    tail.rotation.z = Math.PI / 3
    tail.position.set(-1.2, 0.5, 0)
    tail.castShadow = true
    fox.add(tail)

    // Tail tip (white)
    const tailTipGeometry = new THREE.SphereGeometry(0.25, 8, 8)
    const tailTipMaterial = new THREE.MeshPhongMaterial({ color: 0xffffff })
    const tailTip = new THREE.Mesh(tailTipGeometry, tailTipMaterial)
    tailTip.position.set(-1.8, 0.9, 0)
    fox.add(tailTip)

    // Legs
    const legGeometry = new THREE.CylinderGeometry(0.1, 0.12, 0.8, 8)
    const legMaterial = new THREE.MeshPhongMaterial({ color: 0xff6b35 })

    const legs = [
      { x: 0.4, z: 0.3 },
      { x: 0.4, z: -0.3 },
      { x: -0.4, z: 0.3 },
      { x: -0.4, z: -0.3 },
    ]

    legs.forEach(pos => {
      const leg = new THREE.Mesh(legGeometry, legMaterial)
      leg.position.set(pos.x, -0.6, pos.z)
      leg.castShadow = true
      fox.add(leg)
    })

    // White chest patch
    const chestGeometry = new THREE.SphereGeometry(0.3, 8, 8)
    const chestMaterial = new THREE.MeshPhongMaterial({ color: 0xffffff })
    const chest = new THREE.Mesh(chestGeometry, chestMaterial)
    chest.position.set(0.3, -0.1, 0)
    chest.scale.set(1, 0.8, 0.6)
    fox.add(chest)

    fox.position.set(-3, 1, 0)
    scene.value.add(fox)

    // Store fox for animation
    ;(scene.value as any).fox = fox
  }

  const animate = () => {
    if (!scene.value || !camera.value || !renderer.value) return

    animationId.value = requestAnimationFrame(animate)

    const fox = (scene.value as any).fox
    if (fox) {
      const time = Date.now() * 0.001

      // Walking animation: Move fox across screen
      fox.position.x = -3 + (time % 8)
      
      // Bob up and down while walking
      fox.position.y = 1 + Math.abs(Math.sin(time * 4)) * 0.15

      // Rotate legs for walking effect
      const legs = fox.children.filter((child: THREE.Object3D) => 
        child instanceof THREE.Mesh && child.geometry instanceof THREE.CylinderGeometry
      )
      
      legs.forEach((leg: THREE.Mesh, index: number) => {
        leg.rotation.x = Math.sin(time * 4 + index * Math.PI) * 0.3
      })

      // Tail wag
      const tail = fox.children.find((child: THREE.Object3D) => 
        child instanceof THREE.Mesh && 
        child.geometry instanceof THREE.ConeGeometry &&
        child.position.x < 0
      )
      if (tail) {
        tail.rotation.y = Math.sin(time * 3) * 0.4
      }

      // When fox reaches the end, reset
      if (fox.position.x > 5) {
        fox.position.x = -3
      }
    }

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
