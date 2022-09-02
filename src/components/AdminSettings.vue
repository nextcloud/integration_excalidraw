<template>
	<div id="excalidraw_prefs" class="section">
		<h2>
			<ExcalidrawIcon class="icon" />
			{{ t('integration_excalidraw', 'Excalidraw whiteboard integration') }}
		</h2>
		<div class="field">
			<label for="base-url">
				<EarthIcon :size="20" class="icon" />
				{{ t('integration_excalidraw', 'Excalidraw instance address') }}
			</label>
			<input id="base-url"
				v-model="state.base_url"
				type="text"
				:placeholder="t('integration_excalidraw', 'Instance address')"
				@input="onInput">
		</div>
		<NcCheckboxRadioSwitch
			class="field"
			:checked.sync="state.override_link_click"
			@update:checked="onOverrideChanged">
			{{ t('integration_excalidraw', 'Open Excalidraw links in Nextcloud') }}
		</NcCheckboxRadioSwitch>
	</div>
</template>

<script>
import EarthIcon from 'vue-material-design-icons/Earth.vue'

import ExcalidrawIcon from './icons/ExcalidrawIcon.vue'

import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

import { delay } from '../utils.js'

export default {
	name: 'AdminSettings',

	components: {
		ExcalidrawIcon,
		NcCheckboxRadioSwitch,
		EarthIcon,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_excalidraw', 'admin-config'),
		}
	},

	computed: {
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		onOverrideChanged(newValue) {
			this.saveOptions({ override_link_click: newValue ? '1' : '0' })
		},
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
			axios.put(url, req).then((response) => {
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
	.field {
		display: flex;
		align-items: center;
		margin-left: 30px;

		input,
		label {
			width: 300px;
		}

		label {
			display: flex;
			align-items: center;
		}
		.icon {
			margin-right: 8px;
		}
	}

	h2 {
		display: flex;
		align-items: center;
		.icon {
			margin-right: 12px;
		}
	}
}
</style>
