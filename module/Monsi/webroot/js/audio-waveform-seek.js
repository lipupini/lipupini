document.querySelectorAll('.audio-waveform-seek').forEach(container => {
	const isTouchDevice = 'ontouchstart' in document.documentElement
	const audio = container.querySelector('audio')
	const audioHasThumbnail = !!container.style.backgroundImage
	const waveform = container.querySelector('.waveform')
	const elapsed = waveform.querySelector('.elapsed')
	const transitionDuration = elapsed.style.transitionDuration
	audio.addEventListener('play', () => {
		elapsed.classList.remove('hidden')
	})
	audio.addEventListener('timeupdate', () => {
		elapsed.style.width = ((audio.currentTime / audio.duration) * 100) + '%'
	})
	let trackingMouseMove, touchedOnce = false
	const moveElapsed = (e) => {
		if (elapsed.style.transitionDuration !== 'unset') {
			elapsed.style.transitionDuration = 'unset'
		}
		elapsed.style.width = ((e.layerX / waveform.scrollWidth) * 100) + '%'
	}
	const applyElapsedChange = () => {
		if (!trackingMouseMove || (isTouchDevice && audioHasThumbnail && !touchedOnce)) return
		if (isTouchDevice) {
			waveform.removeEventListener('touchmove', moveElapsed)
		} else {
			waveform.removeEventListener('mousemove', moveElapsed)
		}
		audio.currentTime = ((parseFloat(elapsed.style.width || 0)) / 100) * (audio.duration || 0)
		elapsed.style.transitionDuration = transitionDuration
		trackingMouseMove = false
	}
	waveform.addEventListener('mousedown', (e) => {
		if (isTouchDevice && audioHasThumbnail && touchedOnce === false) {
			touchedOnce = true
			return
		}
		elapsed.classList.remove('hidden')
		elapsed.style.width = ((e.layerX / waveform.scrollWidth) * 100) + '%'
		if (isTouchDevice) {
			waveform.addEventListener('touchmove', moveElapsed)
		} else {
			waveform.addEventListener('mousemove', moveElapsed)
		}
		trackingMouseMove = true
	})
	waveform.addEventListener('mouseleave', applyElapsedChange)
	document.addEventListener('mouseup', applyElapsedChange)
})
