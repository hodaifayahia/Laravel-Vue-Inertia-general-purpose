import { onMounted, onUnmounted } from 'vue'
import gsap from 'gsap'

export function useScrollAnimations(className: string = '.animate-on-scroll') {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          gsap.fromTo(
            entry.target,
            {
              opacity: 0,
              y: 50,
              scale: 0.95,
            },
            {
              opacity: 1,
              y: 0,
              scale: 1,
              duration: 0.8,
              ease: 'power3.out',
            }
          )
          observer.unobserve(entry.target)
        }
      })
    },
    {
      threshold: 0.1,
      rootMargin: '50px',
    }
  )

  onMounted(() => {
    const elements = document.querySelectorAll(className)
    elements.forEach((el) => observer.observe(el))
  })

  onUnmounted(() => {
    observer.disconnect()
  })

  return { observer }
}

export function useCardHoverEffect() {
  const handleMouseMove = (event: MouseEvent, card: HTMLElement) => {
    const rect = card.getBoundingClientRect()
    const x = event.clientX - rect.left
    const y = event.clientY - rect.top
    
    const centerX = rect.width / 2
    const centerY = rect.height / 2
    
    const rotateX = (y - centerY) / 20
    const rotateY = (centerX - x) / 20
    
    gsap.to(card, {
      rotateX: rotateX,
      rotateY: rotateY,
      duration: 0.3,
      ease: 'power2.out',
      transformPerspective: 1000,
    })
  }

  const handleMouseLeave = (card: HTMLElement) => {
    gsap.to(card, {
      rotateX: 0,
      rotateY: 0,
      duration: 0.5,
      ease: 'power2.out',
    })
  }

  return {
    handleMouseMove,
    handleMouseLeave,
  }
}

export function useCounterAnimation(elementId: string, target: number, duration: number = 2000) {
  onMounted(() => {
    const element = document.getElementById(elementId)
    if (!element) return

    const obj = { value: 0 }

    gsap.to(obj, {
      value: target,
      duration: duration / 1000,
      ease: 'power2.out',
      onUpdate: () => {
        element.textContent = Math.round(obj.value).toString()
      },
    })
  })
}

export function useStaggerAnimation(className: string = '.stagger-item') {
  onMounted(() => {
    gsap.fromTo(className,
      {
        opacity: 0,
        y: 30,
        scale: 0.9,
      },
      {
        opacity: 1,
        y: 0,
        scale: 1,
        duration: 0.6,
        stagger: 0.1,
        ease: 'back.out(1.7)',
        delay: 0.2,
      }
    )
  })
}

export function useFloatingAnimation(className: string = '.floating-element') {
  onMounted(() => {
    gsap.to(className, {
      y: 'random(-20, 20)',
      x: 'random(-10, 10)',
      rotation: 'random(-5, 5)',
      duration: 'random(3, 6)',
      ease: 'power2.inOut',
      repeat: -1,
      yoyo: true,
      stagger: {
        amount: 2,
        from: 'random',
      },
    })
  })
}

export function usePulseAnimation(className: string = '.pulse-element') {
  onMounted(() => {
    gsap.to(className, {
      scale: 1.05,
      duration: 2,
      ease: 'power2.inOut',
      repeat: -1,
      yoyo: true,
    })
  })
}

export function useTextRevealAnimation(className: string = '.text-reveal') {
  onMounted(() => {
    const elements = document.querySelectorAll(className)
    elements.forEach((element) => {
      const text = element.textContent || ''
      element.innerHTML = text.split('').map(char =>
        `<span class="char">${char === ' ' ? '&nbsp;' : char}</span>`
      ).join('')

      gsap.fromTo(`${className} .char`,
        {
          opacity: 0,
          y: 20,
          rotateX: -90,
        },
        {
          opacity: 1,
          y: 0,
          rotateX: 0,
          duration: 0.8,
          stagger: 0.02,
          ease: 'back.out(1.7)',
        }
      )
    })
  })
}

export function useParallaxAnimation(className: string = '.parallax-element') {
  const handleScroll = () => {
    const scrolled = window.pageYOffset
    const rate = scrolled * -0.5

    gsap.to(className, {
      y: rate,
      duration: 0.3,
      ease: 'none',
    })
  }

  onMounted(() => {
    window.addEventListener('scroll', handleScroll)
  })

  onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
  })
}
