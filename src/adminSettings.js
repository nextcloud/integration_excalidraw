/* jshint esversion: 6 */

/**
 * Nextcloud - excalidraw
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

import { createApp } from 'vue'
import { translate, translatePlural } from '@nextcloud/l10n'
import AdminSettings from './components/AdminSettings.vue'

// eslint-disable-next-line
'use strict'

const app = createApp(AdminSettings)
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
app.mount('#excalidraw_prefs')
