import van from '/lib/van-1.2.7.min.js'

const { div, video, source } = van.tags

const Video = ({collection, baseUri, filename, data, fileType}) => {
    let attributes = {controls: 'true', preload: 'none', loop: 'true', title: data.caption ?? filename, loading: 'lazy'}
    if (typeof data.poster !== 'undefined') {
        attributes.poster = `${baseUri}file/${collection}/video/poster/${data.poster}`
    }

	return div({class: 'video'},
		video(attributes, source({src: `${baseUri}file/${collection}/video/${filename}#t=0.5`, type: fileType}),
		),
	)
}

export { Video }
