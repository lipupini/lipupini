import van from '/lib/van-1.5.0.min.js'

const { a, div, object } = van.tags

const Text = ({collection, baseUri, filename, data, gridView}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = filename.split('/').map((uriComponent) => encodeURIComponent(uriComponent)).join('/')
	return div({class: 'text-container'},
		gridView ?
			a({href: `/@${collection}/${filenameEncoded}.html`},
				div(data.caption ?? filename.split(/[\\\/]/).pop()),
			) :
			object({type: 'text/html', data: `${baseUri}${collection}/text/${filenameEncoded}.html`})
	)
}

export { Text }
