import van from '/lib/van-1.2.1.min.js'

const { div, a } = van.tags

const Markdown = ({collection, filename, data}) => {
	return div({class: 'markdown'},
		a({href: `/c/file/${collection}/markdown/rendered/${filename.replace(/\.md$/, '.html')}`, target: '_blank', rel: 'noopener noreferrer'},
			div(data.caption ?? filename),
		),
	)
}

export { Markdown }
