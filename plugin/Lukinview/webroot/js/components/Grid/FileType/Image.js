import van from '/lib/van-1.2.1.min.js'

const { div, a, img } = van.tags

const Image = ({collection, filename, data}) => {
	return div(
		a({href: `/c/file/${collection}/large/${filename}`, target: '_blank', rel: 'noopener noreferrer'},
			div({style: 'background-image:url(' + `/c/file/${collection}/small/${filename}` + ')'},
				img({src: '/img/1x1.png', alt: data.caption ?? filename, title: data.caption ?? filename}),
			),
		),
	)
}

export { Image }
