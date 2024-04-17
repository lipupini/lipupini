const ConfigState = config => {
	const localStorageInterface = {
		load: () => JSON.parse(localStorage.getitem('configState') ?? '{}'),
		save: () => localStorage.setItem('configState', JSON.stringify(vanX.compact(state))),
		clear: () => localStorage.removeItem('configState')
	}

	const loaded = localStorageInterface.load()
	const state = vanX.reactive(Object.keys(loaded.length ? loaded : config))
	van.derive(() => localStorageInterface.save())

	return {
		state,
		localStorage: localStorageInterface
	}
}
