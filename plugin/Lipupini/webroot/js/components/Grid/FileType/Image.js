import van from '/lib/van-1.2.1.min.js'

const { div, a, img } = van.tags

const Image = ({url, caption}) => {
	return div(
		a({href: url, target: '_blank', rel: 'noopener noreferrer'},
			div({style: 'background-image:url(' + url + ')'},
				img({src: '/img/1x1.png', alt: caption, title: caption}),
			),
		),
	)
}

export { Image }
