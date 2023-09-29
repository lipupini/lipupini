import van from '/lib/van-1.2.1.min.js'

const { div, a, span } = van.tags

const Folder = ({collection, filename, caption}) => {
	return div({class: 'folder'},
		a({href: `/@${collection}/${filename}`, 'title': caption},
			span(caption),
		),
	)
}

export { Folder }
