document.querySelectorAll('.audio-waveform-seek').forEach(container => {
	let audio = container.querySelector('audio')
	let waveform = container.querySelector('.waveform')
	let elapsed = waveform.querySelector('.elapsed')
	audio.addEventListener('timeupdate', () => {
		elapsed.style.width = ((audio.currentTime / audio.duration) * 100) + '%'
	})
	waveform.addEventListener('click', (e) => {
		audio.currentTime = (e.offsetX / waveform.scrollWidth) * (audio.duration || 0)
	})
})
