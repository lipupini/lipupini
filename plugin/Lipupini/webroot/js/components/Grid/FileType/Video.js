import van from '/lib/van-1.2.1.min.js'

const { div, video, source } = van.tags

const Video = ({collection, filepath, caption}) => {
	return div({class: 'video'},
		video({controls: 'true', preload: 'metadata', loop: 'true', title: caption},
			source({src: `/c/file/${collection}/large/${filepath}#t=0.5`, type: 'video/mp4'}),
		),
	)
}

export { Video }
