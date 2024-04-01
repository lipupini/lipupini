import van from '/lib/van-1.5.0.min.js'

const { div, video, source } = van.tags

const Video = ({collection, baseUri, filename, data, mimeType}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = encodeURIComponent(filename)
	let attributes = {controls: 'true', preload: 'none', loop: 'true', title: data.caption ?? filename.split(/[\\\/]/).pop(), loading: 'lazy'}
	if (typeof data.thumbnail !== 'undefined') {
		attributes.poster = `${baseUri}${collection}/thumbnail/${encodeURIComponent(data.thumbnail)}`
	}
	return div({class: 'video'},
			video(attributes, source({src: `${baseUri}${collection}/video/${filenameEncoded}#t=0.5`, type: mimeType}),
		),
	)
}

export { Video }
