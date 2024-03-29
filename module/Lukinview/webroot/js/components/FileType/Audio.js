import van from '/lib/van-1.5.0.min.js'

const { div, audio, source } = van.tags

const Audio = ({collection, baseUri, filename, data, fileType}) => {
	return div({class: 'audio'},
		audio({controls: 'true', preload: 'metadata', title: data.caption ?? filename.split(/[\\\/]/).pop(), loading: 'lazy'},
			source({src: `${baseUri}${collection}/audio/${filename}`, type: fileType}),
		),
	)
}

export { Audio }
