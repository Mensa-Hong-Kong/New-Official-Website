function store(init) {
    let store = $state(init)
	return {
		get get() {
			return store
		},
		set set(s) {
			store = s;
		}
	}
}

export let submitting = store(false);
