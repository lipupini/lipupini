import { test, expect, Page } from '@playwright/test'

const host = 'http://localhost:4000'

const glob = require('glob')
const path = require('path')
const fs = require('fs')
const testAssetsFolder = __dirname + '/../assets'
const collectionFolder = __dirname + '/../../collection'
const { execSync } = require('child_process');

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

// Find a test collection name that doesn't exist yet
let testCollectionName = 'testcollection';
const updateTestCollectionName = () => {
	let i = 2;
	let testCollectionNameTmp = testCollectionName
	while (fs.existsSync(collectionFolder + '/' + testCollectionNameTmp)) {
		testCollectionNameTmp = testCollectionName + i
		i++;
	}
	testCollectionName = testCollectionNameTmp;
}
updateTestCollectionName();
const testCollectionFolder = collectionFolder + '/' + testCollectionName

const waitForImages = async (page) => {
	for (const img of await page.getByRole('img').all()) {
		await expect(img).toHaveJSProperty('complete', true);
		await expect(img).not.toHaveJSProperty('naturalWidth', 0);
	}
}

const askPhp = arg => {
	return JSON.parse(execSync('php "' + __dirname + '/../../bin/test-helper.php" ' + arg).toString())
}

test('clicks into collection list from homepage', async ({ page }) => {
	await page.goto(host + '/')

	await page.locator('a >> nth=0').click()
	await expect(page).toHaveURL(host + '/@')

	const collections = glob.sync(collectionFolder + '/*')

	for (let i = 0; i < collections.length; i++) {
		if (
			!fs.existsSync(collections[i]) ||
			!fs.lstatSync(collections[i]).isDirectory() ||
			collections[i].charAt(0) === '.'
		) {
			continue;
		}

		collections[i] = path.basename(collections[i])
		await expect(page.locator('li a:text-is("' + collections[i] + '")')).toBeVisible()
	}
})

test('clicks a collection and loads collection page', async ({ page }) => {
	await page.goto(host + '/@')
	const accountName = await page.locator('li a >> nth=0').innerText()
	await page.locator('li a >> nth=0').click()
	await expect(page).toHaveURL(host + '/@' + accountName)
})

test.describe.serial('new collection', () => {
	test('create a new collection', async ({ page }) => {
		// Create the folder
		const testCollectionFolder = collectionFolder + '/' + testCollectionName
		fs.mkdirSync(testCollectionFolder)
		// Populate some test files
		testAssetFiles.forEach(fileName => {
			fs.copyFileSync(testAssetsFolder + '/' + fileName, testCollectionFolder + '/' + fileName)
		})
	})

	test('open new collection in list and view every item', async ({ page }) => {
		await page.goto(host + '/@')
		await expect(page.locator('li a:text-is("' + testCollectionName + '")')).toBeVisible()
		await page.locator('li a:text-is("' + testCollectionName + '")').click()
		await expect(page).toHaveURL(host + '/@' + testCollectionName)
		await waitForImages(page)
		const mediaItems = await page.locator('#folder main.grid > *')
		for (let i = 0; i < await mediaItems.count(); i++) {
			let element = await mediaItems.nth(i);
			console.log(await element.getAttribute('class'))
		}
	})

	test('analyze new collection cache files', async ({page}) => {
		const hasFfmpeg = askPhp('hasFfmpeg')
		console.log('`ffmpeg` is ' + (hasFfmpeg ? 'enabled' : 'not enabled'))
		const collectionCacheFolder = testCollectionFolder + '/.lipupini/cache'
		const imageThumbnailCache = glob.sync(collectionCacheFolder + '/image/thumbnail/*')
		const imageLargeCache = glob.sync(collectionCacheFolder + '/image/large/*')
		console.log(imageThumbnailCache.length, imageLargeCache.length)
	})

	test('test pagination and navigation in header and footer', async ({ page}) => {
		// Add enough files to paginate
		for (let i = 1; i <= 25; i++) {
			fs.copyFileSync(testAssetsFolder + '/blank.png', testCollectionFolder + '/' + i + '.png')
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
})
