import van from '/lib/van-1.2.1.min.js'

const { div, a, img } = van.tags

const Image = ({collection, baseUri, filename, data, background = true}) => {
	let image = img({src: background ? '/img/1x1.png' : `${baseUri}file/${collection}/image/large/${filename}`, alt: data.caption ?? filename, title: data.caption ?? filename, loading: 'lazy'});
	let anchorAttrs = background ? {href:`/@${collection}/${filename}.html`} : {href:`${baseUri}file/${collection}/image/large/${filename}`, target: '_blank'}

	return div({class: 'image'},
		a(anchorAttrs,
			background ? div({style: 'background-image:url("' + `${baseUri}file/${collection}/image/small/${filename}` + '")'},
				image,
			) : image,
		),
	)
}

export { Image }
