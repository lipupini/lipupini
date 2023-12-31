import { Video } from './FileType/Video.js'
import { Image } from './FileType/Image.js'
import { Audio } from './FileType/Audio.js'
import { Text } from './FileType/Text.js'
import { CollectionFolder } from './FileType/CollectionFolder.js'

const Document = ({collection, baseUri, filename, data, gridView = false}) => {
	let extension = filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2)

	if (extension === '') {
		return CollectionFolder({collection, baseUri, filename, data})
	}

	let DocumentComponent;

	Object.keys(fileTypes).forEach(fileType => {
		Object.keys(fileTypes[fileType]).forEach(fileExtension => {
			if (fileExtension === extension) {
				switch (fileType) {
					case 'Audio':
						DocumentComponent = Audio({collection, baseUri, filename, data, fileType: fileTypes[fileType][fileExtension]})
						break
					case 'Video':
						DocumentComponent = Video({collection, baseUri, filename, data, fileType: fileTypes[fileType][fileExtension]})
						break
					case 'Image':
						DocumentComponent = Image({collection, baseUri, filename, data, background: gridView})
						break
					case 'Text':
						DocumentComponent = Text({collection, baseUri, filename, data, load: !gridView})
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
