import van from '/lib/van-1.2.1.min.js'

const { div, a, object } = van.tags

const Markdown = ({collection, baseUri, filename, data, load = false}) => {
	return div({class: 'markdown'},
		load ?
			object({type: 'text/html', data: `${baseUri}file/${collection}/markdown/${filename + '.html'}`}) :
			a({href: `/@${collection}/${filename}.html`},
				div(data.caption ?? filename),
			)
	)
}

export { Markdown }
