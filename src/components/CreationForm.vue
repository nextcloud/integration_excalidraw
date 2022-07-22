<template>
	<div class="creationForm">
		<h2>
			{{ t('integration_excalidraw', 'Create a room') }}
		</h2>
		<div class="fields">
			<div v-for="(field, fieldId) in fields"
				:key="fieldId"
				class="field">
				<div v-if="!['ncSwitch', 'ncCheckbox'].includes(field.type)"
					class="fieldLabelWithIcon">
					<component :is="field.icon"
						v-if="field.icon"
						:size="20" />
					<CheckboxRadioSwitch v-if="field.togglable"
						:checked.sync="field.enabled"
						@update:checked="newBoard[fieldId] = ''">
						{{ field.label }}
					</CheckboxRadioSwitch>
					<label v-else
						:for="'board-' + fieldId">
						{{ field.label }}
					</label>
				</div>
				<span v-else class="fieldLabelWithIcon" />
				<input v-if="field.type === 'text'"
					:id="'board-' + fieldId"
					:ref="'board-' + fieldId"
					v-model="newBoard[fieldId]"
					type="text"
					:placeholder="field.placeholder"
					@keyup.enter="onOkClick">
				<div v-else-if="field.type === 'password' && (!field.togglable || field.enabled)"
					class="password-input-wrapper">
					<input
						:id="'board-' + fieldId"
						v-model="newBoard[fieldId]"
						:type="field.view ? 'text' : 'password'"
						:placeholder="field.placeholder">
					<EyeOutlineIcon v-if="field.view" @click="field.view = false" />
					<EyeOffOutlineIcon v-else @click="field.view = true" />
				</div>
				<span v-else-if="field.type === 'textarea'"
					class="textarea-wrapper">
					<textarea
						:id="'board-' + fieldId"
						v-model="newBoard[fieldId]"
						:placeholder="field.placeholder" />
				</span>
				<DatetimePicker v-else-if="field.type === 'ncDate'"
					:id="'board-' + fieldId"
					v-model="newBoard[fieldId]"
					type="date"
					:placeholder="field.placeholder"
					:clearable="true"
					:confirm="false" />
				<DatetimePicker v-else-if="field.type === 'ncDatetime'"
					:id="'board-' + fieldId"
					v-model="newBoard[fieldId]"
					type="datetime"
					:placeholder="field.placeholder"
					:minute-step="1"
					:clearable="true"
					:confirm="true" />
				<div v-else-if="field.type === 'ncColor'">
					<ColorPicker
						:value="newBoard[fieldId]"
						@input="updateColor($event, fieldId)">
						<Button
							v-tooltip.top="{ content: t('integration_excalidraw', 'Choose color') }"
							:style="{ backgroundColor: newBoard[fieldId] }" />
					</ColorPicker>
				</div>
				<Multiselect v-else-if="field.type === 'select'"
					:value="newBoard[fieldId]"
					:options="Object.values(field.options)"
					label="label"
					:placeholder="field.placeholder"
					@input="setSelectValue(fieldId, $event)"
					@search-change="query = $event">
					<template #option="{option}">
						<component :is="option.icon"
							v-if="option.icon"
							class="option-icon"
							:size="20" />
						<Highlight :text="option.label" :search="query" class="option-title multiselect-option-title" />
					</template>
					<template #singleLabel="{option}">
						<component :is="option.icon"
							v-if="option.icon"
							class="multiselect-label-icon"
							:size="20" />
						<span class="option-title">
							{{ option.label }}
						</span>
					</template>
				</Multiselect>
				<RadioElementSet v-else-if="field.type === 'customRadioSet'"
					:name="fieldId + '_radio'"
					:options="field.options"
					:value="newBoard[fieldId]"
					@update:value="newBoard[fieldId] = $event">
					<!--template #icon="{option}">
						{{ option.label }}
					</template-->
					<!--template-- #label="{option}">
						{{ option.label + 'lala' }}
					</template-->
				</RadioElementSet>
				<div v-else-if="field.type === 'ncRadioSet'">
					<CheckboxRadioSwitch v-for="(option, id) in field.options"
						:key="id"
						:checked.sync="newBoard[fieldId]"
						:value="id"
						:name="fieldId + '_radio'"
						type="radio"
						class="ncradio">
						<component :is="option.icon"
							v-if="option.icon"
							class="option-icon"
							:size="20" />
						<span class="option-title">
							{{ option.label }}
						</span>
					</CheckboxRadioSwitch>
				</div>
				<div v-else-if="field.type === 'ncCheckboxSet'">
					<CheckboxRadioSwitch v-for="(option, id) in field.options"
						:key="id"
						:checked.sync="newBoard[fieldId]"
						:value="id"
						:name="fieldId + '_checkbox'"
						class="ncradio">
						<component :is="option.icon"
							v-if="option.icon"
							class="option-icon"
							:size="20" />
						<span class="option-title">
							{{ option.label }}
						</span>
					</CheckboxRadioSwitch>
				</div>
				<div v-else-if="field.type === 'ncSwitch'">
					<CheckboxRadioSwitch
						:checked.sync="newBoard[fieldId]"
						type="switch"
						class="ncradio">
						<component :is="field.icon"
							v-if="field.icon"
							class="option-icon"
							:size="20" />
						{{ field.label }}
					</CheckboxRadioSwitch>
				</div>
				<div v-else-if="field.type === 'ncCheckbox'">
					<CheckboxRadioSwitch
						:checked.sync="newBoard[fieldId]"
						class="ncradio">
						<component :is="field.icon"
							v-if="field.icon"
							class="option-icon"
							:size="20" />
						{{ field.label }}
					</CheckboxRadioSwitch>
				</div>
			</div>
		</div>
		<div class="excalidraw-footer">
			<div class="spacer" />
			<Button @click="$emit('cancel-clicked')">
				{{ t('integration_excalidraw', 'Cancel') }}
			</Button>
			<Button type="primary" @click="onOkClick">
				<template #icon>
					<CheckIcon :class="{ 'icon-loading': loading }" />
				</template>
				{{ t('integration_excalidraw', 'Create') }}
			</Button>
		</div>
	</div>
</template>

<script>
import EyeOutlineIcon from 'vue-material-design-icons/EyeOutline'
import EyeOffOutlineIcon from 'vue-material-design-icons/EyeOffOutline'
import PaletteIcon from 'vue-material-design-icons/Palette'
import CheckIcon from 'vue-material-design-icons/Check'
import Button from '@nextcloud/vue/dist/Components/Button'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import ColorPicker from '@nextcloud/vue/dist/Components/ColorPicker'
import DatetimePicker from '@nextcloud/vue/dist/Components/DatetimePicker'
import Highlight from '@nextcloud/vue/dist/Components/Highlight'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import { showError } from '@nextcloud/dialogs'

import { fields } from '../utils'
import RadioElementSet from './RadioElementSet'

export default {
	name: 'CreationForm',

	components: {
		RadioElementSet,
		CheckIcon,
		PaletteIcon,
		EyeOutlineIcon,
		EyeOffOutlineIcon,
		Button,
		Multiselect,
		DatetimePicker,
		ColorPicker,
		Highlight,
		CheckboxRadioSwitch,
	},

	props: {
		loading: {
			type: Boolean,
			default: false,
		},
		focusOnField: {
			type: String,
			default: null,
		},
	},

	data() {
		return {
			newBoard: {},
			fields,
			query: '',
		}
	},

	computed: {
	},

	watch: {
	},

	beforeMount() {
		const boardWithDefaults = {}
		Object.keys(fields).forEach((fieldId) => {
			boardWithDefaults[fieldId] = fields[fieldId].default
		})
		this.newBoard = {
			...boardWithDefaults,
		}
	},

	mounted() {
		if (this.focusOnField) {
			const fields = this.$refs['board-' + this.focusOnField]
			if (fields && fields.length > 0) {
				this.$nextTick(() => {
					fields[0].focus()
					fields[0].select()
				})
			}
		}
	},

	methods: {
		onOkClick() {
			let isFormValid = true
			Object.keys(this.fields).forEach((fieldId) => {
				const field = this.fields[fieldId]
				const fieldValue = this.newBoard[fieldId]
				// a field with false as value is accepted
				if (field.mandatory && !fieldValue && fieldValue !== false) {
					showError(t('integration_excalidraw', 'Field "{name}" is missing', { name: field.label }))
					isFormValid = false
				}
			})
			if (isFormValid) {
				this.$emit('ok-clicked', {
					...this.newBoard,
				})
			}
		},
		setSelectValue(fieldId, newValue) {
			// this fixes the issue when selecting the currently select option
			if (newValue !== null) {
				this.$set(this.newBoard, fieldId, newValue)
			}
		},
		updateColor(color, fieldId) {
			this.newBoard[fieldId] = color
		},
	},
}
</script>

<style scoped lang="scss">
.creationForm {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 12px;

	.fields {
		display: flex;
		flex-direction: column;
		.field {
			display: flex;
			align-items: center;
			justify-content: center;
			flex-wrap: wrap;
			margin: 5px 0 5px 0;
			padding: 8px;
			border-radius: var(--border-radius-large);
			&:hover {
				background-color: var(--color-background-hover);
			}
			> * {
				margin: 4px;
				&:last-child {
					width: 250px;
				}
			}
			.fieldLabelWithIcon {
				display: flex;
				align-items: center;
				width: 250px;
				label {
					width: 150px;
				}
				> * {
					margin: 0 8px 0 8px;
				}
			}
			.password-input-wrapper {
				display: flex;
				align-items: center;
				input {
					flex-grow: 1;
				}
			}
			.option-icon {
				margin-left: 4px;
				margin-right: 8px;
			}
			.multiselect-label-icon {
				margin-right: 12px;
			}
			.option-title {
				// nothing
			}
			.multiselect-option-title {
				text-overflow: ellipsis;
				overflow: hidden;
				white-space: nowrap;
			}
			.textarea-wrapper {
				textarea {
					height: 65px;
					width: 250px;
					max-width: 250px;
				}
			}
			// this fixes the multiline radio label
			:deep(.ncradio > label) {
				height: unset !important;
				min-height: 44px;
				> * {
					margin-top: 8px;
					margin-bottom: 8px;
				}
			}
		}
	}

	.spacer {
		flex-grow: 1;
	}

	.excalidraw-footer {
		width: 100%;
		display: flex;
		align-items: center;
		margin-top: 12px;
		> * {
			margin-left: 8px;
		}
	}
}
</style>
