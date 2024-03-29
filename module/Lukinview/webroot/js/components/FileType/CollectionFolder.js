import van from '/lib/van-1.5.0.min.js'

const { div, a, span } = van.tags

const CollectionFolder = ({collection, baseUri, filename, data}) => {
	let title = data.caption ?? filename.split(/[\\\/]/).pop();
	return div({class: 'folder'},
		a({href: `/@${collection}/${filename}`, 'title': title}, span(title)),
	)
}

export { CollectionFolder }
