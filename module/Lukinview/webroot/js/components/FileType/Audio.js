import van from '/lib/van-1.5.0.min.js'

const { a, audio, div, source } = van.tags

const Audio = ({collection, baseUri, filename, data, mimeType, gridView}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = filename.split('/').map((uriComponent) => encodeURIComponent(uriComponent)).join('/')
	let title = data.caption ?? filename.split(/[\\\/]/).pop();
	let thumbnailAttributes = {
		class: 'thumbnail', style: typeof data.thumbnail !== 'undefined' ?
			'background-image:url("' + `${baseUri}${collection}/thumbnail/${encodeURIComponent(data.thumbnail)}` + '")' : ''
	};
	if (gridView) {
		thumbnailAttributes.href = `/@${collection}/${filenameEncoded}.html`;
	}
	let captionDiv = div({class: 'caption'}, title);
	return div({class: 'audio', title: title},
		gridView ? a(thumbnailAttributes, captionDiv) : div(thumbnailAttributes, captionDiv),
		audio(
			{controls: 'true', preload: 'metadata', loading: 'lazy'},
			source({src: `${baseUri}${collection}/audio/${filenameEncoded}`, type: mimeType}),
		)
	)
}

export { Audio }
