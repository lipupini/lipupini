import van from '/lib/van-1.2.1.min.js'
import { Document } from './Document.js';

const Folder = ({collection, collectionData, baseUri}) => {
	Object.keys(collectionData).forEach(filename => {
		van.add(document.getElementById('media-grid'), Document({collection, baseUri, filename, data: collectionData[filename], gridView: true}))
	})
}

export { Folder }
