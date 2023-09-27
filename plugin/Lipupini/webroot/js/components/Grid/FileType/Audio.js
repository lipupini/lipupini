import van from '/lib/van-1.2.1.min.js'

const { div, audio, source } = van.tags

const Audio = ({url, caption}) => {
	return div({class: 'audio'},
		audio({controls: 'true', preload: 'metadata', title: caption},
			source({src: `${url}#t=0.5`, type: 'video/mp4'}),
		),
	)
}

export { Audio }
