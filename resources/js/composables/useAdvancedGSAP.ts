import { onMounted, onUnmounted } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

/**
 * Advanced GSAP animations with ScrollTrigger
 * Inspired by modern web design patterns
 */

export function useHeroTextReveal(selector: string = '.hero-headline') {
  onMounted(() => {
    const element = document.querySelector(selector)
    if (!element) return

    if ((element as HTMLElement).dataset.heroSplit === 'true') {
      return
    }

    // Split text into words
    const lineElements = element.querySelectorAll('[data-hero-line]')
    let animationTargets: Element[] = []

    if (lineElements.length) {
      lineElements.forEach((line) => {
        const text = (line.textContent || '').trim()
        const words = text.split(/\s+/)

        line.innerHTML = words
          .map(word => `<span class="word-reveal"><span class="word-inner">${word}</span></span>`)
          .join(' ')

        const innerSpans = line.querySelectorAll('.word-reveal > .word-inner')
        animationTargets = animationTargets.concat(Array.from(innerSpans))
      })
    } else {
      const text = element.textContent || ''
      const words = text.split(/\s+/)

      element.innerHTML = words
        .map(word => `<span class="word-reveal"><span class="word-inner">${word}</span></span>`)
        .join(' ')

      animationTargets = Array.from(element.querySelectorAll('.word-reveal > .word-inner'))
    }

    ;(element as HTMLElement).dataset.heroSplit = 'true'

    gsap.from(animationTargets, {
      duration: 1.2,
      opacity: 0,
      y: 30,
      filter: 'blur(20px)',
      stagger: {
        amount: 0.8,
        ease: 'power2.out'
      },
      ease: 'power3.out',
      delay: 0.3
    })
  })
}

export function useScrollReveal(selector: string = '.reveal-text') {
  onMounted(() => {
    const elements = document.querySelectorAll(selector)
    
    elements.forEach((element) => {
      gsap.from(element, {
        scrollTrigger: {
          trigger: element,
          start: 'top 85%',
          end: 'bottom 60%',
          toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: 'power2.out'
      })
    })
  })
}

export function useCharacterReveal(selector: string = '.split-reveal') {
  onMounted(() => {
    const elements = document.querySelectorAll(selector)
    
    elements.forEach((element) => {
      const text = element.textContent || ''
      const chars = text.split('')
      
      element.innerHTML = chars
        .map(char => `<span class="char-reveal" style="display: inline-block; opacity: 0;">${char === ' ' ? '&nbsp;' : char}</span>`)
        .join('')

      const charSpans = element.querySelectorAll('.char-reveal')

      gsap.to(charSpans, {
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          end: 'bottom 60%',
          scrub: 1
        },
        opacity: 1,
        filter: 'blur(0px)',
        stagger: 0.02,
        duration: 0.5
      })
    })
  })
}

export function useFadeScale(selector: string = '.fade-scale') {
  onMounted(() => {
    const elements = document.querySelectorAll(selector)
    
    elements.forEach((element) => {
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
  })
}

export function useParallaxElements(selector: string = '[data-speed]') {
  onMounted(() => {
    const elements = document.querySelectorAll(selector)
    
    elements.forEach((element) => {
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
          const height = target.getBoundingClientRect().height
          return -height * (1 - speed) * 0.5
        },
        ease: 'none'
      })
    })
  })
}

export function usePinSection(selector: string = '.pin-section') {
  onMounted(() => {
    const sections = document.querySelectorAll(selector)
    
    sections.forEach((section) => {
      const textElements = section.querySelectorAll('.reveal-text')
      
      ScrollTrigger.create({
        trigger: section,
        start: 'top top',
        end: 'bottom top',
        pin: true,
        pinSpacing: true,
        animation: gsap.timeline()
          .from(textElements, {
            opacity: 0,
            y: 50,
            duration: 1,
            stagger: 0.2,
            ease: 'power2.out'
          })
      })
    })
  })
}

export function useHorizontalScroll(selector: string = '.horizontal-scroll') {
  onMounted(() => {
    const container = document.querySelector(selector)
    if (!container) return

    const sections = gsap.utils.toArray(`${selector} > *`)
    
    gsap.to(sections, {
      xPercent: -100 * (sections.length - 1),
      ease: 'none',
      scrollTrigger: {
        trigger: container,
        pin: true,
        scrub: 1,
        snap: 1 / (sections.length - 1),
        end: () => `+=${(container as HTMLElement).offsetWidth}`
      }
    })
  })
}

export function useTextSplitStagger(selector: string = '.text-stagger') {
  onMounted(() => {
    const elements = document.querySelectorAll(selector)
    
    elements.forEach((element) => {
      const text = element.textContent || ''
      const words = text.split(' ')
      
      element.innerHTML = words
        .map(word => `<span style="display: inline-block; opacity: 0;">${word}</span>`)
        .join(' ')

      const wordSpans = element.querySelectorAll('span')

      gsap.to(wordSpans, {
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          toggleActions: 'play none none reverse'
        },
        opacity: 1,
        y: 0,
        duration: 0.8,
        stagger: 0.05,
        ease: 'back.out(1.7)'
      })
    })
  })
}

export function useCleanupScrollTrigger() {
  onUnmounted(() => {
    ScrollTrigger.getAll().forEach(trigger => trigger.kill())
  })
}

export function useRotateOnScroll(selector: string = '.rotate-scroll') {
  onMounted(() => {
    const elements = document.querySelectorAll(selector)
    
    elements.forEach((element) => {
      gsap.to(element, {
        scrollTrigger: {
          trigger: element,
          start: 'top bottom',
          end: 'bottom top',
          scrub: true
        },
        rotation: 360,
        ease: 'none'
      })
    })
  })
}

export function useMorphOnScroll(selector: string = '.morph-scroll') {
  onMounted(() => {
    const elements = document.querySelectorAll(selector)
    
    elements.forEach((element) => {
      gsap.from(element, {
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          end: 'bottom 20%',
          scrub: 1
        },
        scale: 0.8,
        borderRadius: '50%',
        rotation: -10,
        ease: 'power2.out'
      })
    })
  })
}
