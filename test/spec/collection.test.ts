import { test, expect, Page } from '@playwright/test'

const host = 'http://localhost:4000'

const glob = require('glob')
const path = require('path')
const fs = require('fs')
const testAssetsFolder = __dirname + '/../assets'
const collectionRootFolder = __dirname + '/../../collection'
const { execSync } = require('child_process')

// If `createNewCollection` is `true`, the `testCollectionName` will have a number appended if it exists
let testCollectionName = 'testcollection'

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

const askPhp = to => {
	return JSON.parse(execSync('php "' + __dirname + '/../../bin/test-helper.php" ' + to).toString())
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

test('clicks into collection list from homepage', async ({ page }) => {
	await page.goto(host + '/')

	await page.locator('a >> nth=0').click()
	await expect(page).toHaveURL(host + '/@')

	const collections = glob.sync(testCollectionFolder.root + '/*')

	for (let i = 0; i < collections.length; i++) {
		if (
			!fs.existsSync(collections[i]) ||
			!fs.lstatSync(collections[i]).isDirectory() ||
			collections[i].charAt(0) === '.'
		)  continue

		collections[i] = path.basename(collections[i])
		await expect(page.locator('li a:text-is("' + collections[i] + '")')).toBeVisible()
	}
})

test.describe.serial('test collection', () => {
	if (createNewCollection) {
		test('create a new test collection', async ({page}) => {
			// Create the folder
			fs.mkdirSync(testCollectionFolder.root)
			// Populate some test files
			testAssetFiles.forEach(fileName => {
				fs.copyFileSync(testAssetsFolder + '/' + fileName, testCollectionFolder.root + '/' + fileName)
			})
		})
	}

	if (createNewCollection) {
		test('add custom assets', async () => {
			fs.cpSync(testAssetsFolder + '/image', testCollectionFolder.root + '/.lipupini/image', {recursive: true})
			if (hasFfmpeg) {
				// If we have `ffmpeg` then we only need to copy custom assets that aren't generated
				fs.mkdirSync(testCollectionFolder.root + '/.lipupini/audio', {recursive: true})
				fs.cpSync(testAssetsFolder + '/audio/thumbnail', testCollectionFolder.root + '/.lipupini/audio/thumbnail', {recursive: true})
			} else {
				fs.cpSync(testAssetsFolder + '/audio', testCollectionFolder.root + '/.lipupini/audio', {recursive: true})
				fs.cpSync(testAssetsFolder + '/video', testCollectionFolder.root + '/.lipupini/video', {recursive: true})
			}
		})
	}

	test('open collection in list and view every item', async ({ page }) => {
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
					await page.goto(await page.locator('main a').getAttribute('href'))
					break
				case 'video':
				case 'audio':
					await page.waitForLoadState('load')
					break
			}
		}
	})

	test('analyze collection cache files', async () => {
		const analyzeCache = askPhp('analyzeCache ' + testCollectionName)
		await expect(analyzeCache.messages).toEqual(undefined)
	})

	if (testCollectionPagination) {
		test('test pagination and navigation in header and footer', async ({page}) => {
			if (createNewCollection) {
				// Add enough files to paginate
				for (let i = 1; i <= 25; i++) {
					fs.copyFileSync(testAssetsFolder + '/blank.png', testCollectionFolder.root + '/' + i + '.png')
				}
			}
			for (const navLocation of ['header', 'footer']) {
				await page.goto(host + '/@' + testCollectionName)
				await expect(page.locator(navLocation + ' nav .previous .button')).toHaveAttribute('disabled')
				await page.locator(navLocation + ' nav .next .button').click()
				await expect(page).toHaveURL(host + '/@' + testCollectionName + '?page=2')
				await expect(page.locator(navLocation + ' nav .next .button')).toHaveAttribute('disabled')
				await page.locator(navLocation + ' nav .previous .button').click()
				await expect(page).toHaveURL(host + '/@' + testCollectionName)
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
