import van from '/lib/van-1.2.1.min.js'
import { Video } from './FileType/Video.js'
import { Image } from './FileType/Image.js'
import { Audio } from './FileType/Audio.js'
import { Markdown } from './FileType/Markdown.js'
import { Folder } from './FileType/Folder.js'

const Grid = ({collection, collectionData}) => {
	Object.keys(collectionData).reverse().forEach(filename => {
		switch (filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2)) {
			case 'jpg':
			case 'png':
				van.add(document.getElementById('media-container'), Image({collection, filename, data: collectionData[filename]}))
				break
			case 'mp4':
				van.add(document.getElementById('media-container'), Video({collection, filename, data: collectionData[filename]}))
				break
			case 'mp3':
				van.add(document.getElementById('media-container'), Audio({collection, filename, data: collectionData[filename]}))
				break
			case 'md':
				van.add(document.getElementById('media-container'), Markdown({collection, filename, data: collectionData[filename]}))
				break
			case '':
				van.add(document.getElementById('media-container'), Folder({collection, filename, data: collectionData[filename]}))
				break
			default:
				throw new Error('Unknown file extension')
		}
	})
}

Grid({ collection, collectionData })
