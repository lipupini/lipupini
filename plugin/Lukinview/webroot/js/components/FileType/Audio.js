import van from '/lib/van-1.2.1.min.js'

const { div, audio, source } = van.tags

const Audio = ({collection, filename, data}) => {
	return div({class: 'audio'},
		audio({controls: 'true', preload: 'metadata', title: data.caption ?? filename},
			source({src: `/c/file/${collection}/audio/${filename}`, type: 'audio/mp3'}),
		),
	)
}

export { Audio }
