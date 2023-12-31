import van from '/lib/van-1.2.7.min.js'

const { div, video, source } = van.tags

const Video = ({collection, baseUri, filename, data, fileType}) => {
	return div({class: 'video'},
		video({controls: 'true', preload: 'none', loop: 'true', title: data.caption ?? filename, loading: 'lazy', poster: data.thumbnail ?? null},
			source({src: `${baseUri}file/${collection}/video/${filename}#t=0.5`, type: fileType}),
		),
	)
}

export { Video }
