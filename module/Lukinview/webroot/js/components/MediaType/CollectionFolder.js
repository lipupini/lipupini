import van from '/lib/van-1.5.0.min.js'

const { div, a, span } = van.tags

const CollectionFolder = ({collection, filename, data}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = filename.split('/').map((uriComponent) => encodeURIComponent(uriComponent)).join('/')
	let title = data.caption ?? filename.split(/[\\\/]/).pop()
	return div(
		{class: 'folder-container'},
		a({href: `/@${collection}/${filenameEncoded}`, 'title': title}, span(title)),
	)
}

export { CollectionFolder }
