import van from '/lib/van-1.2.1.min.js'

const { div, a, img } = van.tags

const Image = ({collection, filename, data, background = true}) => {
	let image = img({src: background ? '/img/1x1.png' : `/c/file/${collection}/image/large/${filename}`, alt: data.caption ?? filename, title: data.caption ?? filename});
	let anchorAttrs = background ? {href:`/@${collection}/${filename}.html`} : {href:`/c/file/${collection}/image/large/${filename}`, target: '_blank'}

	return div({class: 'image'},
		a(anchorAttrs,
			background ? div({style: 'background-image:url(' + `/c/file/${collection}/image/small/${filename}` + ')'},
				image,
			) : image,
		),
	)
}

export { Image }
