import van from '/lib/van-1.2.7.min.js'

const { div, a, img } = van.tags

const Image = ({collection, baseUri, filename, data, background = true}) => {
	let image = img({src: background ? '/img/1x1.png' : `${baseUri}${collection}/image/large/${filename}`, title: data.caption ?? filename.split(/[\\\/]/).pop(), loading: 'lazy'});
	let anchorAttrs = background ? {href:`/@${collection}/${filename}.html`} : {href:`${baseUri}${collection}/image/large/${filename}`, target: '_blank'}

	return div({class: 'image'},
		a(anchorAttrs,
			background ? div({style: 'background-image:url("' + `${baseUri}${collection}/image/thumbnail/${filename}` + '")'},
				image,
			) : image,
		),
	)
}

export { Image }
