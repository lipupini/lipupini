document.querySelectorAll('.audio-waveform-seek').forEach(container => {
	const audio = container.querySelector('audio')
	const waveform = container.querySelector('.waveform')
	const elapsed = waveform.querySelector('.elapsed')
	const transitionDuration = elapsed.style.transitionDuration
	audio.addEventListener('play', () => {
		elapsed.classList.remove('hidden')
	})
	audio.addEventListener('timeupdate', () => {
		elapsed.style.width = ((audio.currentTime / audio.duration) * 100) + '%'
	})
	let trackingMouseMove = false
	const moveElapsed = (e) => {
		if (elapsed.style.transitionDuration !== 'unset') {
			elapsed.style.transitionDuration = 'unset'
		}
		elapsed.style.width = ((e.layerX / waveform.scrollWidth) * 100) + '%'
	}
	const applyElapsedChange = () => {
		waveform.removeEventListener('mousemove', moveElapsed)
		audio.currentTime = ((parseFloat(elapsed.style.width || 0)) / 100) * (audio.duration || 0)
		elapsed.style.transitionDuration = transitionDuration
		trackingMouseMove = false
	}
	waveform.addEventListener('mousedown', (e) => {
		elapsed.classList.remove('hidden')
		elapsed.style.width = ((e.layerX / waveform.scrollWidth) * 100) + '%'
		waveform.addEventListener('mousemove', moveElapsed)
		trackingMouseMove = true
	})
	waveform.addEventListener('mouseleave', applyElapsedChange)
	document.addEventListener('mouseup', applyElapsedChange)
})
