import { Video } from './FileType/Video.js'
import { Image } from './FileType/Image.js'
import { Audio } from './FileType/Audio.js'
import { Markdown } from './FileType/Markdown.js'
import { CollectionFolder } from './FileType/CollectionFolder.js'

const Document = ({collection, filename, data, gridView = false}) => {
	let extension = filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2)
	switch (extension) {
		case 'jpg':
		case 'png':
			return Image({collection, filename, data, background: gridView})
		case 'mp4':
			return Video({collection, filename, data})
		case 'mp3':
			return Audio({collection, filename, data})
		case 'md':
			return Markdown({collection, filename, data, load: !gridView})
		case '':
			return CollectionFolder({collection, filename, data})
		default:
			throw new Error('Unknown file extension: ' + extension)
	}
}

export { Document }
