import { shallowRef, onMounted, onUnmounted } from 'vue'
import * as THREE from 'three'
import gsap from 'gsap'

/**
 * Advanced Three.js Growing Tree Animation
 * Tree grows from seed to full tree as user scrolls
 */
export function useThreeJsGrowingTree(containerId: string) {
  const scene = shallowRef<THREE.Scene | null>(null)
  const camera = shallowRef<THREE.PerspectiveCamera | null>(null)
  const renderer = shallowRef<THREE.WebGLRenderer | null>(null)
  const tree = shallowRef<THREE.Group | null>(null)
  const animationId = shallowRef<number | null>(null)

  const init = () => {
    const container = document.getElementById(containerId)
    if (!container) return

    // Scene setup
    scene.value = new THREE.Scene()
    scene.value.background = null

    // Camera setup
    camera.value = new THREE.PerspectiveCamera(
      45,
      container.clientWidth / container.clientHeight,
      0.1,
      1000
    )
    camera.value.position.set(0, 3, 8)
    camera.value.lookAt(0, 2, 0)

    // Renderer setup
    renderer.value = new THREE.WebGLRenderer({ alpha: true, antialias: true })
    renderer.value.setSize(container.clientWidth, container.clientHeight)
    renderer.value.setClearColor(0x000000, 0)
    container.appendChild(renderer.value.domElement)

    // Create tree
    tree.value = new THREE.Group()
    
    // Trunk
    const trunkGeometry = new THREE.CylinderGeometry(0.15, 0.2, 3, 8)
    const trunkMaterial = new THREE.MeshPhongMaterial({ 
      color: 0x4a3020,
      flatShading: true
    })
    const trunk = new THREE.Mesh(trunkGeometry, trunkMaterial)
    trunk.position.y = 1.5
    tree.value.add(trunk)

    // Branches
    const createBranch = (y: number, rotation: number, scale: number) => {
      const branchGeometry = new THREE.CylinderGeometry(0.05 * scale, 0.08 * scale, 0.8 * scale, 6)
      const branch = new THREE.Mesh(branchGeometry, trunkMaterial)
      branch.position.set(0, y, 0)
      branch.rotation.z = rotation
      tree.value!.add(branch)

      // Leaves on branch
      const leavesGeometry = new THREE.SphereGeometry(0.3 * scale, 8, 8)
      const leavesMaterial = new THREE.MeshPhongMaterial({ 
        color: 0x2d5016,
        flatShading: true
      })
      const leaves = new THREE.Mesh(leavesGeometry, leavesMaterial)
      leaves.position.set(
        Math.sin(rotation) * 0.4 * scale,
        y + 0.3,
        Math.cos(rotation) * 0.4 * scale
      )
      tree.value!.add(leaves)
    }

    // Multiple branches at different heights
    const branchLevels = [
      { y: 2.2, scale: 1 },
      { y: 2.6, scale: 0.9 },
      { y: 3.0, scale: 0.8 },
      { y: 3.3, scale: 0.7 }
    ]

    branchLevels.forEach((level, index) => {
      const angleOffset = (index * Math.PI * 0.5)
      for (let i = 0; i < 4; i++) {
        const angle = (Math.PI * 2 * i / 4) + angleOffset
        createBranch(level.y, angle, level.scale)
      }
    })

    // Tree top
    const topGeometry = new THREE.ConeGeometry(0.8, 1.5, 8)
    const topMaterial = new THREE.MeshPhongMaterial({ 
      color: 0x3a7021,
      flatShading: true
    })
    const top = new THREE.Mesh(topGeometry, topMaterial)
    top.position.y = 3.8
    tree.value.add(top)

    // Ground
    const groundGeometry = new THREE.CircleGeometry(2, 32)
    const groundMaterial = new THREE.MeshPhongMaterial({ 
      color: 0x4a8b3a,
      side: THREE.DoubleSide
    })
    const ground = new THREE.Mesh(groundGeometry, groundMaterial)
    ground.rotation.x = -Math.PI / 2
    ground.position.y = 0
    tree.value.add(ground)

    // Initial scale for growth animation
    tree.value.scale.set(0, 0, 0)
    
    scene.value.add(tree.value)

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6)
    scene.value.add(ambientLight)

    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8)
    directionalLight.position.set(5, 10, 5)
    scene.value.add(directionalLight)

    const fillLight = new THREE.DirectionalLight(0xb8d4ff, 0.4)
    fillLight.position.set(-5, 3, -5)
    scene.value.add(fillLight)

    // Growth animation
    gsap.to(tree.value.scale, {
      x: 1,
      y: 1,
      z: 1,
      duration: 2,
      ease: 'elastic.out(1, 0.5)',
      delay: 0.5
    })

    // Gentle swaying animation
    const sway = () => {
      if (tree.value) {
        gsap.to(tree.value.rotation, {
          z: Math.sin(Date.now() * 0.001) * 0.05,
          duration: 2,
          ease: 'sine.inOut',
          onComplete: sway
        })
      }
    }
    sway()

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

      if (tree.value) {
        tree.value.rotation.y += 0.002
      }

      if (renderer.value && scene.value && camera.value) {
        renderer.value.render(scene.value, camera.value)
      }
    }

    animate()

    return () => {
      if (animationId.value) {
        cancelAnimationFrame(animationId.value)
      }
      if (renderer.value && container) {
        container.removeChild(renderer.value.domElement)
        renderer.value.dispose()
      }
      window.removeEventListener('resize', handleResize)
    }
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
    tree
  }
}
