import van from '/lib/van-1.2.1.min.js'
import { Video } from './FileType/Video.js'
import { Image } from './FileType/Image.js'
import { Audio } from './FileType/Audio.js'

const Grid = ({collectionData}) => {
	collectionData.forEach((item) => {
		const params = { url: item.fileUrl, caption: item.caption }
		switch (item.fileUrl.slice((item.fileUrl.lastIndexOf(".") - 1 >>> 0) + 2)) {
			case 'jpg':
			case 'png':
				van.add(document.getElementById('media-container'), Image(params))
				break
			case 'mp4':
				van.add(document.getElementById('media-container'), Video(params))
				break
			case 'mp3':
				van.add(document.getElementById('media-container'), Audio(params))
				break
			default:
				throw new Error('Unknown file extension')
		}
	})
}

Grid({ collectionData })
