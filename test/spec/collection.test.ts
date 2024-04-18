import {test, expect, Page} from '@playwright/test'

const host = 'http://localhost:4000' // Without trailing slash

import glob = require('glob')
import path = require('path')
import fs = require('fs')
const testAssetsFolder = __dirname + '/../assets'
const collectionRootFolder = __dirname + '/../../collection'
import {execSync} from 'child_process'

// If `createNewCollection` is `true`, the `testCollectionName` will have a number appended if it exists
let testCollectionName = 'test-collection'

const createNewCollection = true
const testCollectionPagination = true
const deleteNewCollection = true

const testAssetFiles = [
	'animated.gif',
	'blank.jpg',
	'blank.png',
	'dup.mp4',
	'logo_static.gif',
	'test.avif',
	'toki-ipsum.md',
	'beep.ogg',
	'huddle-invite.m4a',
	'needs-moar.jpeg',
	'test.flac',
	'winamp-intro.mp3'
]

const askPhp = (to: string) => {
	const command = 'php "' + __dirname + '/../../bin/test-helper.php" ' + to
	const answer = execSync(command, {stdio: 'pipe'}).toString()
	try {
		return JSON.parse(answer)
	} catch (e) {
		console.log(answer)
		throw e
	}
}

const supportsAvif = async (page: Page) => {
	return await page.evaluate(async () => {
		if (typeof createImageBitmap === 'undefined') return false
		const avifData = 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgANogQEAwgMg8f8D///8WfhwB8+ErK42A='
		const avifBlob = await fetch(avifData).then((r) => r.blob());
		return createImageBitmap(avifBlob)
			.then(() => true)
			.catch(() => false)
	})
}

const hasFfmpeg = askPhp('determineFfmpegSupport')
//console.log('`ffmpeg` is ' + (hasFfmpeg ? 'enabled' : 'not enabled'))

let testCollectionFolder: any = {
	root: collectionRootFolder + '/' + testCollectionName,
	cache: collectionRootFolder + '/' + testCollectionName + '/.lipupini/.cache'
}

// Find a test collection name that doesn't exist yet
if (createNewCollection) {
	let i = 2
	let testCollectionNameTmp = testCollectionName
	while (fs.existsSync(collectionRootFolder + '/' + testCollectionNameTmp)) {
		testCollectionNameTmp = testCollectionName + i
		i++
	}
	testCollectionName = testCollectionNameTmp
	testCollectionFolder.root = collectionRootFolder + '/' + testCollectionName
	testCollectionFolder.cache = testCollectionFolder.root + '/.lipupini/.cache'
}

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
			// Create the folder
			fs.mkdirSync(testCollectionFolder.root)
			const supportsAvifResult = await supportsAvif(page)
			// Populate some test files
			for (const fileName of testAssetFiles) {
				const extension = fileName.match(/\.(.+)$/)[1]
				if (extension === 'avif' && !supportsAvifResult) continue
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
				fs.mkdirSync(testCollectionFolder.root + '/.lipupini/audio', {recursive: true})
				fs.cpSync(testAssetsFolder + '/audio/thumbnail', testCollectionFolder.root + '/.lipupini/audio/thumbnail', {recursive: true})
			} else {
				fs.cpSync(testAssetsFolder + '/audio', testCollectionFolder.root + '/.lipupini/audio', {recursive: true})
				fs.cpSync(testAssetsFolder + '/video', testCollectionFolder.root + '/.lipupini/video', {recursive: true})
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
		await page.waitForTimeout(2000) // Because we're also testing on-demand static generation which adds some stress
		let hrefs = []
		const mediaItemLinks = await page.locator('#folder main.grid a').all()
		for (const mediaItemLink of mediaItemLinks) {
			hrefs.push(await mediaItemLink.getAttribute('href'))
		}
		for (const href of hrefs) {
			await page.goto(host + href)
			await page.waitForTimeout(750) // Because we're also testing on-demand static generation which adds some stress
			let mediaType = (await page.locator('#media-item').getAttribute('class')).replace(/-item/, '')
			switch (mediaType) {
				case 'image':
					await page.goto(await page.locator('main a').getAttribute('href'))
					break
				case 'audio':
					const audioSrc = await page.locator('main source').getAttribute('src')
					expect((await request.get(audioSrc)).ok()).toBeTruthy()
					const waveform = (await page.locator('main .waveform').getAttribute('style'))
						.match(/background-image:url\('(.+)'\);?/)[1]
					expect((await request.get(waveform)).ok()).toBeTruthy()
					break
				case 'video':
					const videoSrc = await page.locator('main source').getAttribute('src')
					expect((await request.get(videoSrc)).ok()).toBeTruthy()
					const videoPoster = await page.locator('main video').getAttribute('poster')
					expect((await request.get(videoPoster)).ok()).toBeTruthy()
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
		const supportsAvifResult = await supportsAvif(page)
		expect(Object.keys(apiResponseBody.data).length).toEqual(supportsAvifResult ? 12 : 11)
		expect((await request.get(apiUrl + '/blank.png.json')).ok()).toBeTruthy()
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
				const supportsAvifResult = await supportsAvif(page)
				// Add enough files to paginate
				for (let i = 1; i <= (supportsAvifResult ? 25 : 26); i++) {
					fs.copyFileSync(testAssetsFolder + '/blank.png', testCollectionFolder.root + '/' + i + '.png')
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
			await page.goto(host + '/@')
			await expect(page.locator('li a:text-is("' + testCollectionName + '")')).toBeHidden()
		})
	}
})
