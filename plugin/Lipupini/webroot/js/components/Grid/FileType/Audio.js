import van from '/lib/van-1.2.1.min.js'

const { div, audio, source } = van.tags

const Audio = ({collection, filepath, caption}) => {
	return div({class: 'audio'},
		audio({controls: 'true', preload: 'metadata', title: caption},
			source({src: `/c/file/${collection}/large/${filepath}`, type: 'audio/mp3'}),
		),
	)
}

export { Audio }
