import { createApp } from 'vue'
import App from './App.vue'

import '../css/main.scss'
import { translate, translatePlural } from '@nextcloud/l10n'

document.addEventListener('DOMContentLoaded', (event) => {
	const app = createApp(App)
	app.mixin({
		methods: {
			t: translate,
			n: translatePlural,
		},
		computed: {
			OCA() {
				return window.OCA
			},
			OC() {
				return window.OC
			},
		},
	})
	app.mount('#content')
})
