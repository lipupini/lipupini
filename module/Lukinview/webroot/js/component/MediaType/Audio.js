import van from '/lib/van-1.5.0.min.js'

const { a, audio, div, source } = van.tags

const Audio = ({collection, baseUri, filename, data, mimeType, gridView}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = filename.split('/').map((uriComponent) => encodeURIComponent(uriComponent)).join('/')
	let title = data.caption ?? filename.split(/[\\\/]/).pop()
	let captionDiv = div({class: 'caption'}, title)
	let style = typeof data.thumbnail !== 'undefined' ?
		'background-image:url("' + encodeURI(data.thumbnail) + '")' : ''
	let audioElement = audio(
		{controls: 'true', preload: 'metadata', loading: 'lazy'},
		source({src: `${baseUri}${collection}/audio/${filenameEncoded}`, type: mimeType}),
	)
	return gridView ?
		a({class: 'audio-container', href: `/@${collection}/${filenameEncoded}.html`, style}, captionDiv, audioElement) :
		div({class: 'audio-container', style,
			onclick: (e) => {
				let audio = e.target.closest('.audio-container').querySelector('audio')
				audio.paused ? audio.play() : audio.pause()
			}
		}, captionDiv, audioElement)
}

export { Audio }
