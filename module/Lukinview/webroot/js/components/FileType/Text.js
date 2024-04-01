import van from '/lib/van-1.5.0.min.js'

const { a, div, object } = van.tags

const Text = ({collection, baseUri, filename, data, gridView}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = encodeURIComponent(filename)
	return div({class: 'text'},
		gridView ?
			a({href: `/@${collection}/${filenameEncoded}.html`},
				div(data.caption ?? filename.split(/[\\\/]/).pop()),
			) :
			object({type: 'text/html', data: `${baseUri}${collection}/text/${filenameEncoded}.html`})
	)
}

export { Text }
