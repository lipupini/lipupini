import { test, expect, Page } from '@playwright/test'

const host = 'http://localhost:4000'

const glob = require('glob')
const path = require('path')
const fs = require('fs')

const navigateToFirstAccount = async (page: Page) => {
	await page.goto(host + '/')
	const accountName = await page.locator('li a >> nth=0').innerText()
	await page.locator('li a >> nth=0').click()
	await expect(page).toHaveURL(host + '/@' + accountName)
}

test('displays list of accounts on homepage', async ({ page }) => {
	await page.goto(host + '/')

	const collectionFolder = __dirname + '/../..'
	const collections = glob.sync(collectionFolder + '/collection/*')

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

test('clicks an account and loads account page', async ({ page }) => {
	await navigateToFirstAccount(page)
})

/*test('opens settings', async ({ page }) => {
	// Settings are per-account, have to go to an account page
	await navigateToFirstAccount(page)
	await expect(page.locator('#settings-form')).not.toBeVisible()
	await page.locator('#button-container-settings button').click()
	await expect(page.locator('#settings-form')).toBeVisible()
})*/

