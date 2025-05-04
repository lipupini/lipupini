document.querySelectorAll('.audio-container').forEach(container => {
	const mediaElement = container.querySelector('audio')
	container.classList.add('loading')
	mediaElement.addEventListener('loadedmetadata', () => container.classList.remove('loading'))
	// Check again after timeout in case the `loadedmetadata` event fired very quickly (e.g. via caching)
	setTimeout(() => {
		if (mediaElement.readyState > 1) {
			container.classList.remove('loading')
		}
	}, 250)
	mediaElement.addEventListener('play', () => container.classList.add('playing'))
	mediaElement.addEventListener('pause', () => container.classList.remove('playing'))
})
