import van from '/lib/van-1.5.0.min.js'

const { a, div, video, source } = van.tags

const Video = ({collection, baseUri, filename, data, mimeType}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = filename.split('/').map((uriComponent) => encodeURIComponent(uriComponent)).join('/')
	let attributes = {controls: 'true', preload: 'metadata', loop: 'true', title: data.caption ?? filename.split(/[\\\/]/).pop(), loading: 'lazy'}
	if (typeof data.thumbnail !== 'undefined') {
		attributes.poster = encodeURI(data.thumbnail)
	}
	return div({class: 'video-container'},
			div({class: 'caption'}, a({href: `/@${collection}/${filenameEncoded}.html`}, data.caption)),
			video(attributes, source({src: `${baseUri}${collection}/video/${filenameEncoded}`, type: mimeType})
		),
	)
}

export { Video }
