document.querySelectorAll('.audio-container').forEach(container => {
	const isTouchDevice = 'ontouchstart' in document.documentElement
	const audio = container.querySelector('audio')
	const waveform = container.querySelector('.waveform')
	const elapsed = waveform.querySelector('.elapsed')
	const transitionDuration = elapsed.style.transitionDuration
	let trackingMouseMove = false

	audio.addEventListener('play', () => {
		elapsed.classList.remove('hidden')
	})

	audio.addEventListener('timeupdate', () => {
		if (trackingMouseMove) return
		elapsed.style.width = ((audio.currentTime / audio.duration) * 100) + '%'
	})

	const moveElapsed = (e) => {
		if (elapsed.style.transitionDuration !== 'unset') {
			elapsed.style.transitionDuration = 'unset'
		}
		const xVal = isTouchDevice ? e.touches[0].clientX - waveform.getBoundingClientRect().x : e.layerX
		elapsed.style.width = ((xVal / waveform.scrollWidth) * 100) + '%'
	}

	const applyElapsedChange = (e) => {
		if (!trackingMouseMove) return
		if (isTouchDevice) {
			waveform.removeEventListener('touchmove', moveElapsed)
		} else {
			waveform.removeEventListener('mousemove', moveElapsed)
		}
		trackingMouseMove = false
		if (e.type === 'touchcancel') return
		audio.currentTime = ((parseFloat(elapsed.style.width || '0')) / 100) * (audio.duration || 0)
		elapsed.style.transitionDuration = transitionDuration
	}

	const mouseDownTouchStart = (e) => {
		/* See CSS hover state for waveform, on mobile it takes effect on touch */
		if (isTouchDevice && window.getComputedStyle(waveform).opacity === '0') return
		if (e.target.tagName !== 'DIV') return
		elapsed.classList.remove('hidden')
		elapsed.style.width = ((e.layerX / waveform.scrollWidth) * 100) + '%'
		if (isTouchDevice) {
			waveform.addEventListener('touchmove', moveElapsed, {passive: true})
		} else {
			waveform.addEventListener('mousemove', moveElapsed)
		}
		trackingMouseMove = true
	}

	waveform.addEventListener('touchstart', mouseDownTouchStart, {passive: true})
	waveform.addEventListener('mousedown', mouseDownTouchStart)
	waveform.addEventListener('touchend', applyElapsedChange)
	waveform.addEventListener('touchcancel', applyElapsedChange)
	waveform.addEventListener('mouseleave', applyElapsedChange)
	document.addEventListener('mouseup', applyElapsedChange)
})
