import { Video } from './MediaType/Video.js'
import { Image } from './MediaType/Image.js'
import { Audio } from './MediaType/Audio.js'
import { Text } from './MediaType/Text.js'
import { CollectionFolder } from './MediaType/CollectionFolder.js'

const Document = ({collection, baseUri, filename, data, gridView = false}) => {
	let extension = filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2)
	if (extension === '') {
		return CollectionFolder({collection, filename, data})
	}
	let DocumentComponent
	Object.keys(fileTypes).forEach(fileType => {
		Object.keys(fileTypes[fileType]).forEach(fileExtension => {
			if (fileExtension === extension) {
				switch (fileType) {
					case 'audio':
						DocumentComponent = Audio({collection, baseUri, filename, data, mimeType: fileTypes[fileType][fileExtension], gridView})
						break
					case 'video':
						DocumentComponent = Video({collection, baseUri, filename, data, mimeType: fileTypes[fileType][fileExtension]})
						break
					case 'image':
						DocumentComponent = Image({collection, baseUri, filename, data, gridView})
						break
					case 'text':
						DocumentComponent = Text({collection, baseUri, filename, data, gridView})
						break
					default:
						throw new Error('Unknown file extension: ' + extension)
				}
			}
		})
	})
	return DocumentComponent
}

export { Document }
