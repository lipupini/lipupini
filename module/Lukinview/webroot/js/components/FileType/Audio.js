import van from '/lib/van-1.5.0.min.js'

const { a, audio, div, source } = van.tags

const Audio = ({collection, baseUri, filename, data, mimeType, gridView}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = encodeURIComponent(filename)
	let title = data.caption ?? filename.split(/[\\\/]/).pop();
	return div(
		{class: 'audio'},
		div({class: 'thumbnail', style: typeof data.thumbnail !== 'undefined' ?
			'background-image:url("' + `${baseUri}${collection}/thumbnail/${encodeURIComponent(data.thumbnail)}` + '")' : ''}),
		div({class: 'caption'}, gridView ? a({href: `/@${collection}/${filenameEncoded}.html`}, title) : title),
		audio({controls: 'true', preload: 'metadata', title: title, loading: 'lazy'},
			source({src: `${baseUri}${collection}/audio/${filenameEncoded}`, type: mimeType}),
		),
	)
}

export { Audio }
