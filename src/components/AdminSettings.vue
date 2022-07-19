<template>
	<div id="excalidraw_prefs" class="section">
		<h2>
			<ExcalidrawIcon />
			{{ t('integration_excalidraw', 'Excalidraw whiteboard integration') }}
		</h2>
		<Button @click="modalOpen = true">
			<template #icon>
				<ExcalidrawIcon />
			</template>
			{{ t('integration_excalidraw', 'Open modal') }}
		</Button>
		<Modal v-if="modalOpen"
			size="full"
			@close="modalOpen = false">
			<iframe
				class="frame"
				frameborder="0"
				:allowFullScreen="true"
				src="https://excalidraw.com/#room=976bb714733bb0b0ca3d,jkVi7KC6Bj5cxkpX2MI1LA" />
		</Modal>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import Modal from '@nextcloud/vue/dist/Components/Modal'
import Button from '@nextcloud/vue/dist/Components/Button'

import { delay } from '../utils'
import ExcalidrawIcon from './icons/ExcalidrawIcon'

export default {
	name: 'AdminSettings',

	components: {
		ExcalidrawIcon,
		Modal,
		Button,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_excalidraw', 'admin-config'),
			modalOpen: false,
		}
	},

	computed: {
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		onInput() {
			delay(() => {
				this.saveOptions({ base_url: this.state.base_url })
			}, 2000)()
		},
		saveOptions(values) {
			const req = {
				values,
			}
			const url = generateUrl('/apps/integration_excalidraw/admin-config')
			axios.put(url, req)
				.then((response) => {
					showSuccess(t('integration_excalidraw', 'Excalidraw admin options saved'))
				}).catch((error) => {
					showError(
						t('integration_excalidraw', 'Failed to save Excalidraw options')
						+ ': ' + error.response?.request?.responseText
					)
					console.error(error)
				}).then(() => {
				})
		},
	},
}
</script>

<style scoped lang="scss">
#excalidraw_prefs {
	h2 {
		display: flex;
		align-items: center;
	}
}

.frame {
	width: 100%;
	height: 100%;
}
</style>
