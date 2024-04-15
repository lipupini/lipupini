import van from '/lib/van-1.5.0.min.js'

const { div, a, img } = van.tags

const Image = ({collection, baseUri, filename, data, gridView}) => {
	collection = encodeURIComponent(collection)
	let filenameEncoded = filename.split('/').map((uriComponent) => encodeURIComponent(uriComponent)).join('/')
	let image = img({src: gridView ? '/img/1x1.png' : `${baseUri}${collection}/image/large/${filenameEncoded}`, title: data.caption ?? filename.split(/[\\\/]/).pop(), loading: 'lazy'})
	let anchorAttrs = gridView ? {href: `/@${collection}/${filenameEncoded}.html`} : {href: `${baseUri}${collection}/image/large/${filenameEncoded}`, target: '_blank'}
	anchorAttrs.class = 'image-container'
	return a(anchorAttrs, gridView ? div({style: 'background-image:url("' + `${baseUri}${collection}/image/thumbnail/${filenameEncoded}` + '")'}, image) : image)
}

export { Image }
