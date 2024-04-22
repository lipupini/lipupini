import {test, expect, Page} from '@playwright/test'

const host = 'http://localhost:4000' // Without trailing slash

import glob = require('glob')
import path = require('path')
import fs = require('fs')
const testAssetsFolder = __dirname + '/../assets'
const collectionRootFolder = __dirname + '/../../collection'
const webRootFolder = __dirname + '/../../module/Lukinview/webroot'
import {execSync} from 'child_process'

// In Chromiom version 119.0.6045.9 bundled with Playwright,
// there is an issue when loading the .flac example
// See version info with:
// cd ~/.cache/ms-playwright/chromium-1084/chrome-linux && ./chrome --version
const chromiumDisableFlac = true

// If `createNewCollection` is `true`, the `testCollectionName` will have a number appended if it exists
let testCollectionName = 'test-collection'

const createNewCollection = true
const testCollectionPagination = true
const deleteNewCollection = true

const testCollectionFiles: string[] = [
	'animated.gif',
	'blank.jpg',
	'!ñ\'@ #$+á%é^&().png',
	'dup.mp4',
	'logo_static.gif',
	'toki-ipsum.md',
	'huddle-invite.m4a',
	'needs-moar.jpeg',
	'winamp-intro.mp3',
]

const testCollectionCustomAssets: string[] = [
	'audio/thumbnail/winamp-intro.mp3.jpg',
	'image/thumbnail/blank.jpg',
	'video/thumbnail/dup.mp4.jpg',
	'audio/waveform/beep.ogg.png',
	'audio/waveform/huddle-invite.m4a.png',
	'audio/waveform/winamp-intro.mp3.png',
]

const askPhp = (to: string): any => {
	const command = 'php "' + __dirname + '/../../bin/test-helper.php" ' + to
	const answer = execSync(command, {stdio: 'pipe'}).toString()
	try {
		return JSON.parse(answer)
	} catch (e) {
		console.log(answer)
		throw e
	}
}

const itemsPerPage: number = askPhp('getItemsPerPage')

const hasFfmpeg: boolean = askPhp('determineFfmpegSupport')
//console.log('`ffmpeg` is ' + (hasFfmpeg ? 'enabled' : 'not enabled'))

let testCollectionFolder: any = {
	root: collectionRootFolder + '/' + testCollectionName,
	cache: collectionRootFolder + '/' + testCollectionName + '/.lipupini/.cache'
}

let totalTestAssetsUsed = testCollectionFiles.length

test.beforeAll(async ({browser}) => {
	const page = await (await browser.newContext()).newPage()

	// Find a test collection name that doesn't exist yet
	if (createNewCollection) {
		let i = 2
		let testCollectionNameTmp = testCollectionName + '-' + browser.browserType().name()
		while (fs.existsSync(collectionRootFolder + '/' + testCollectionNameTmp)) {
			testCollectionNameTmp = testCollectionName + '-' + browser.browserType().name() + i
			i++
		}
		testCollectionName = testCollectionNameTmp
		testCollectionFolder.root = collectionRootFolder + '/' + testCollectionName
		testCollectionFolder.cache = testCollectionFolder.root + '/.lipupini/.cache'
	}

	if (await page.evaluate(async () => {
		if (typeof createImageBitmap === 'undefined') return false
		const avifData = 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgANogQEAwgMg8f8D///8WfhwB8+ErK42A='
		const avifBlob = await fetch(avifData).then((r) => r.blob());
		return createImageBitmap(avifBlob)
			.then(() => true)
			.catch(() => false)
	})) {
		testCollectionFiles.push('test.avif')
		totalTestAssetsUsed++
	}

	if ((browser.browserType().name() !== 'chromium' || !chromiumDisableFlac) &&
		await page.evaluate(async () => {
		const audio = document.createElement('audio');
		return audio.canPlayType('audio/wav') !== ''
	})) {
		testCollectionFiles.push('test.flac')
		totalTestAssetsUsed++
	}
})

test('click into collection list from homepage and verify all', async ({ page}) => {
	await page.goto(host + '/')

	await page.locator('a >> nth=0').click()
	await expect(page).toHaveURL(host + '/@')

	const collections = glob.sync(collectionRootFolder + '/*')

	for (let i = 0; i < collections.length; i++) {
		if (
			!fs.existsSync(collections[i]) ||
			!fs.lstatSync(collections[i]).isDirectory() ||
			collections[i].charAt(0) === '.'
		)  continue

		await expect(page.locator('li a:text-is("' + path.basename(collections[i]) + '")')).toBeVisible()
	}
})

test.describe.serial('test collection', () => {
	if (createNewCollection) {
		test('creates a new test collection', async ({page}) => {
			console.log('Using collection name ' + testCollectionName)
			// Create the collection root folder
			fs.mkdirSync(testCollectionFolder.root)
			// Create the collection's `.lipupini` folder
			fs.mkdirSync(testCollectionFolder.root + '/.lipupini')
			// Generate RSA keys
			const generateKeysResult = askPhp('generateKeys ' + testCollectionName)
			expect(generateKeysResult.messages).toEqual(undefined)
			// Populate some test files
			for (const fileName of testCollectionFiles) {
				fs.copyFileSync(testAssetsFolder + '/' + fileName, testCollectionFolder.root + '/' + fileName)
			}
			await page.waitForTimeout(500) // A little delay to help ensure that the new files are available
		})
	}

	if (createNewCollection) {
		test('add custom assets', async ({page}) => {
			fs.cpSync(testAssetsFolder + '/image', testCollectionFolder.root + '/.lipupini/image', {recursive: true})
			if (hasFfmpeg) {
				// If we have `ffmpeg` then we only need to copy custom assets that aren't generated
				fs.mkdirSync(testCollectionFolder.root + '/.lipupini/audio')
				fs.cpSync(testAssetsFolder + '/audio/thumbnail', testCollectionFolder.root + '/.lipupini/audio/thumbnail', {recursive: true})
			} else {
				testCollectionCustomAssets.forEach(assetPath => {
					fs.cpSync(testAssetsFolder + '/' + assetPath, testCollectionFolder.root + '/.lipupini/' + assetPath)
				})
				if (testCollectionFiles.indexOf('test.flac') > -1) {
					fs.cpSync(testAssetsFolder + '/audio/waveform/test.flac.png', testCollectionFolder.root + '/.lipupini/audio/waveform/test.flac.png')
				}
			}
			await page.waitForTimeout(500) // A little delay to help ensure that the new files are available
		})
	}

	test('open collection in list and view every item', async ({ page, request }) => {
		test.slow()
		await page.goto(host + '/@')
		await expect(page.locator('li a:text-is("' + testCollectionName + '")')).toBeVisible()
		await page.locator('li a:text-is("' + testCollectionName + '")').click()
		await page.waitForURL(host + '/@' + testCollectionName)
		let hrefs = []
		const mediaItemLinks = await page.locator('#folder main.grid a').all()
		for (const mediaItemLink of mediaItemLinks) {
			hrefs.push(await mediaItemLink.getAttribute('href'))
		}
		for (const href of hrefs) {
			await page.goto(host + href)
			let mediaType = (await page.locator('#media-item').getAttribute('class')).replace(/-item/, '')
			switch (mediaType) {
				case 'image':
					const largeImg = await page.locator('main a').getAttribute('href')
					const mediumImg= await page.locator('main img').getAttribute('src')
					await page.goto(largeImg)
					await page.goto(mediumImg)
					break
				case 'audio':
					const audioSrc = await page.locator('main source').getAttribute('src')
					const waveform = (await page.locator('main .waveform').getAttribute('style'))
						.match(/background-image:url\('(.+)'\);?/)[1]
					await page.goto(waveform)
					await page.goto(audioSrc, {waitUntil: 'commit'})
					await page.waitForURL(audioSrc)
					break
				case 'text':
					await page.goto(await page.locator('main object').getAttribute('data'))
					break
				case 'video':
					const videoSrc = await page.locator('main source').getAttribute('src')
					const videoPoster = await page.locator('main video').getAttribute('poster')
					await page.goto(videoPoster)
					await page.goto(videoSrc)
					await page.waitForURL(videoSrc)
					break
			}
		}
	})

	test('check WebFinger URL', async ({page, request}) => {
		expect(
			(await request.get(
					host + '/.well-known/webfinger?resource=acct:' + testCollectionName + '@' + host.replace(/^https?:\/\//, ''))
			).ok()
		).toBeTruthy()
	})

	test('check RSS URLs', async ({page, request}) => {
		const rssUrl = host + '/rss/' + testCollectionName + '/' + testCollectionName + '-feed.rss'
		await page.goto(host + '/@' + testCollectionName)
		await expect(page.locator('link[rel="alternate"][type="application/rss+xml"]')).toHaveAttribute('href', rssUrl)
		expect((await request.get(rssUrl)).ok()).toBeTruthy()
	})

	test('check API URLs', async ({page, request}) => {
		const apiUrl = host + '/api/' + testCollectionName
		const apiResponse = await request.get(apiUrl)
		expect(apiResponse.ok()).toBeTruthy()
		const apiResponseBody = JSON.parse((await apiResponse.body()).toString());
		expect(Object.keys(apiResponseBody.data).length).toEqual(totalTestAssetsUsed)
		expect((await request.get(apiUrl + '/blank.jpg.json')).ok()).toBeTruthy()
	})

	test('check ActivityPub endpoints', async ({page, request}) => {
		const apBaseUrl = host + '/ap/' + testCollectionName
		expect((await request.get(apBaseUrl)).status()).toEqual(302)
		expect((await request.get(apBaseUrl + '/followers')).ok()).toBeTruthy()
		expect((await request.get(apBaseUrl + '/following')).ok()).toBeTruthy()
		expect((await request.post(apBaseUrl + '/inbox')).status()).toEqual(400)
		expect((await request.get(apBaseUrl + '/outbox')).ok()).toBeTruthy()
		expect((await request.get(apBaseUrl + '/profile')).ok()).toBeTruthy()
		expect((await request.post(apBaseUrl + '/sharedInbox')).status()).toEqual(400)

		expect((await request.get(host + '/.well-known/nodeinfo')).ok()).toBeTruthy()
		expect((await request.get(host + '/.well-known/nodeinfo?local')).ok()).toBeTruthy()
	})

	test('analyze collection cache files', async () => {
		const analyzeCacheResult = askPhp('analyzeCache ' + testCollectionName)
		console.log(analyzeCacheResult)
		expect(analyzeCacheResult.messages).toEqual(undefined)
	})

	if (testCollectionPagination) {
		test('test pagination and navigation in header and footer', async ({page}) => {
			if (createNewCollection) {
				// Add just enough files to paginate into the next page
				for (let i = 1; i <= itemsPerPage - totalTestAssetsUsed + 1; i++) {
					fs.copyFileSync(testAssetsFolder + '/blank.jpg', testCollectionFolder.root + '/' + i + '.jpg')
				}
			}
			for (const navLocation of ['header', 'footer']) {
				await page.goto(host + '/@' + testCollectionName)
				await expect(page.locator(navLocation + ' nav .previous .button')).toHaveAttribute('disabled')
				await page.locator(navLocation + ' nav .next .button').click()
				await page.waitForURL(host + '/@' + testCollectionName + '?page=2')
				await expect(page.locator(navLocation + ' nav .next .button')).toHaveAttribute('disabled')
				await page.locator(navLocation + ' nav .previous .button').click()
				await page.waitForURL(host + '/@' + testCollectionName)
			}
		})
	}

	if (createNewCollection && deleteNewCollection) {
		test('delete test collection', async ({page}) => {
			// Delete the folder
			fs.rmSync(testCollectionFolder.root, {recursive: true})
			fs.rmSync(webRootFolder + '/c/' + testCollectionName)
			await page.goto(host + '/@')
			await expect(page.locator('li a:text-is("' + testCollectionName + '")')).toBeHidden()
		})
	}
})
