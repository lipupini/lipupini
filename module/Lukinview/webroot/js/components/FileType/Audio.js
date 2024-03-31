import van from '/lib/van-1.5.0.min.js'

const { audio, div, source } = van.tags

const Audio = ({collection, baseUri, filename, data, mimeType}) => {
	let title = data.caption ?? filename.split(/[\\\/]/).pop();
	return div(
		{class: 'audio'},
		div({class: 'thumbnail', style: typeof data.thumbnail !== 'undefined' ?
			'background-image:url("' + `${baseUri}${collection}/thumbnail/${data.thumbnail}` + '")' : ''}),
		div({class: 'caption'}, title),
		audio({controls: 'true', preload: 'metadata', title: title, loading: 'lazy'},
			source({src: `${baseUri}${collection}/audio/${filename}`, type: mimeType}),
		),
	)
}

export { Audio }
