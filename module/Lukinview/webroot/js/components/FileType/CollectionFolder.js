import van from '/lib/van-1.5.0.min.js'

const { div, a, span } = van.tags

const CollectionFolder = ({collection, baseUri, filename, data}) => {
	return div({class: 'folder'},
		a({href: `/@${collection}/${filename}`, 'title': data.caption ?? filename},
			span(data.caption ?? filename.split(/[\\\/]/).pop()),
		),
	)
}

export { CollectionFolder }
