import van from '/lib/van-1.2.1.min.js'

const { div, a, object } = van.tags

const Markdown = ({collection, filename, data, load = false}) => {
	return div({class: 'markdown'},
		load ?
			object({type: 'text/html', data: `/c/file/${collection}/markdown/rendered/${filename.replace(/\.md$/, '.html')}`}) :
			a({href: `${filename}.html`},
				div(data.caption ?? filename),
			)
	)
}

export { Markdown }
