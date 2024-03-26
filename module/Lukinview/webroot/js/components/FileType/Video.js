import van from '/lib/van-1.2.7.min.js'

const { div, video, source } = van.tags

const Video = ({collection, baseUri, filename, data, fileType}) => {
	let attributes = {controls: 'true', preload: 'none', loop: 'true', title: data.caption ?? filename, loading: 'lazy'}
	if (typeof data.thumbnail !== 'undefined') {
		attributes.poster = `${baseUri}${collection}/thumbnail/${data.thumbnail}`
	}

	return div({class: 'video'},
		video(attributes, source({src: `${baseUri}${collection}/video/${filename}#t=0.5`, type: fileType}),
		),
	)
}

export { Video }
