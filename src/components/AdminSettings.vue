<template>
	<div id="excalidraw_prefs" class="section">
		<h2>
			<ExcalidrawIcon class="icon" />
			{{ t('integration_excalidraw', 'Excalidraw whiteboard integration') }}
		</h2>
		<Button @click="modalOpen = true">
			<template #icon>
				<ExcalidrawIcon />
			</template>
			{{ t('integration_excalidraw', 'Open modal') }}
		</Button>
		<ExcalidrawModal v-if="modalOpen"
			:board-url="boardUrl"
			@close="modalOpen = false" />
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import Button from '@nextcloud/vue/dist/Components/Button'

import { delay } from '../utils'
import ExcalidrawIcon from './icons/ExcalidrawIcon'
import ExcalidrawModal from './ExcalidrawModal'

export default {
	name: 'AdminSettings',

	components: {
		ExcalidrawModal,
		ExcalidrawIcon,
		Button,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_excalidraw', 'admin-config'),
			modalOpen: false,
			boardUrl: 'https://excalidraw.com/#room=976bb714733bb0b0ca3d,jkVi7KC6Bj5cxkpX2MI1LA',
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
		.icon {
			margin-right: 12px;
		}
	}
}

.frame {
	width: 100%;
	height: 100%;
}
</style>
