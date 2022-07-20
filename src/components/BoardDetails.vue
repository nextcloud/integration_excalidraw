<template>
	<div class="details-wrapper">
		<div class="boardDetails">
			<h2>
				{{ board.name }}
			</h2>
			<div class="links">
				<div class="link">
					<LinkVariantIcon :size="20" />
					<label>
						{{ t('integration_excalidraw', 'Public board link') }}
					</label>
					<div class="linkInputWrapper">
						<input type="text" :readonly="true" :value="publicLink">
						<a :href="publicLink" @click.prevent.stop="copyLink(false)">
							<Button v-tooltip.bottom="{ content: t('integration_excalidraw', 'Copy to clipboard') }">
								<template #icon>
									<CheckIcon v-if="publicLinkCopied"
										class="copiedIcon"
										:size="16" />
									<ClippyIcon v-else
										:size="16" />
								</template>
							</Button>
						</a>
					</div>
				</div>
				<div v-if="talkEnabled"
					class="talk-button-wrapper">
					<Button @click="showTalkModal = true">
						<template #icon>
							<TalkIcon :size="20" />
						</template>
						{{ t('integration_excalidraw', 'Share link to a Talk conversation') }}
					</Button>
					<SendModal v-if="showTalkModal"
						:board="board"
						:excalidraw-url="excalidrawUrl"
						@close="showTalkModal = false" />
				</div>
			</div>
			<div class="fields">
				<div v-for="(field, fieldId) in fieldsToDisplay"
					:key="fieldId"
					class="field">
					<component :is="field.icon"
						v-if="field.icon"
						:size="20" />
					<span v-else class="emptyIcon" />
					<label class="fieldLabel">
						{{ field.label }}
					</label>
					<label v-if="['ncCheckbox'].includes(field.type)"
						:id="'board-' + fieldId + '-value'"
						class="fieldValue multiple">
						<component :is="field.enabledIcon"
							v-if="board[fieldId] && field.enabledIcon"
							:size="20" />
						<component :is="field.disabledIcon"
							v-else-if="!board[fieldId] && field.disabledIcon"
							:size="20" />
						<CheckboxMarkedIcon v-else-if="board[fieldId]" :size="20" />
						<CheckboxBlankOutlineIcon v-else-if="!board[fieldId]" :size="20" />
						{{ board[fieldId] ? t('integration_excalidraw', 'Enabled') : t('integration_excalidraw', 'Disabled') }}
					</label>
					<label v-if="['ncSwitch'].includes(field.type)"
						:id="'board-' + fieldId + '-value'"
						class="fieldValue multiple">
						<component :is="field.enabledIcon"
							v-if="board[fieldId] && field.enabledIcon"
							:size="20" />
						<component :is="field.disabledIcon"
							v-else-if="!board[fieldId] && field.disabledIcon"
							:size="20" />
						<ToggleSwitchIcon v-else-if="board[fieldId]" :size="20" />
						<ToggleSwitchOffOutlineIcon v-else-if="!board[fieldId]" :size="20" />
						{{ board[fieldId] ? t('integration_excalidraw', 'Enabled') : t('integration_excalidraw', 'Disabled') }}
					</label>
					<label v-if="['text'].includes(field.type)"
						:id="'board-' + fieldId + '-value'"
						class="fieldValue">
						{{ board[fieldId] }}
					</label>
					<div v-if="['password'].includes(field.type)" class="password-input-wrapper">
						<label
							:id="'board-' + fieldId + '-value'"
							class="fieldValue">
							{{ field.view ? board[fieldId] : discify(board[fieldId]) }}
						</label>
						<EyeOutlineIcon v-if="field.view" @click="field.view = false" />
						<EyeOffOutlineIcon v-else @click="field.view = true" />
					</div>
					<label v-else-if="['ncDate'].includes(field.type)"
						:id="'board-' + fieldId + '-value'"
						class="fieldValue">
						{{ getFormattedDate(board[fieldId]) }}
					</label>
					<label v-else-if="['ncDatetime'].includes(field.type)"
						:id="'board-' + fieldId + '-value'"
						class="fieldValue">
						{{ getFormattedDatetime(board[fieldId]) }}
					</label>
					<label v-else-if="['ncColor'].includes(field.type)"
						:id="'board-' + fieldId + '-value'"
						class="fieldValue">
						<div class="colorDot" :style="{ 'background-color': board[fieldId] }" />
					</label>
					<textarea v-if="['textarea'].includes(field.type)"
						:id="'board-' + fieldId + '-value'"
						class="fieldValue"
						:value="board[fieldId]"
						:readonly="true" />
					<label v-else-if="['select', 'customRadioSet', 'ncRadioSet'].includes(field.type)"
						:for="'board-' + fieldId + '-value'"
						class="fieldValue multiple">
						<component :is="field.options[board[fieldId]].icon"
							v-if="field.options[board[fieldId]].icon"
							:size="20" />
						{{ field.options[board[fieldId]].label }}
					</label>
					<label v-else-if="['ncCheckboxSet'].includes(field.type)"
						:for="'board-' + fieldId + '-value'"
						class="fieldValue multipleVertical">
						<div v-for="optionId in board[fieldId]"
							:key="optionId"
							class="oneValue">
							<component :is="field.options[optionId].icon"
								v-if="field.options[optionId].icon"
								:size="20" />
							{{ field.options[optionId].label }}
						</div>
					</label>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { fields, Timer } from '../utils'
import moment from '@nextcloud/moment'
import ShieldLinkVariantIcon from 'vue-material-design-icons/ShieldLinkVariant'
import LinkVariantIcon from 'vue-material-design-icons/LinkVariant'
import ToggleSwitchIcon from 'vue-material-design-icons/ToggleSwitch'
import ToggleSwitchOffOutlineIcon from 'vue-material-design-icons/ToggleSwitchOffOutline'
import CheckboxMarkedIcon from 'vue-material-design-icons/CheckboxMarked'
import CheckboxBlankOutlineIcon from 'vue-material-design-icons/CheckboxBlankOutline'
import CheckIcon from 'vue-material-design-icons/Check'
import EyeOutlineIcon from 'vue-material-design-icons/EyeOutline'
import EyeOffOutlineIcon from 'vue-material-design-icons/EyeOffOutline'

import TalkIcon from './talk/TalkIcon'
import ClippyIcon from './icons/ClippyIcon'

import Button from '@nextcloud/vue/dist/Components/Button'
import { showSuccess, showError } from '@nextcloud/dialogs'
import SendModal from './talk/SendModal'

export default {
	name: 'BoardDetails',

	components: {
		ClippyIcon,
		SendModal,
		TalkIcon,
		LinkVariantIcon,
		ShieldLinkVariantIcon,
		ToggleSwitchIcon,
		ToggleSwitchOffOutlineIcon,
		CheckboxBlankOutlineIcon,
		CheckboxMarkedIcon,
		CheckIcon,
		EyeOutlineIcon,
		EyeOffOutlineIcon,
		Button,
	},

	props: {
		board: {
			type: Object,
			required: true,
		},
		excalidrawUrl: {
			type: String,
			required: true,
		},
		talkEnabled: {
			type: Boolean,
			default: false,
		},
	},

	data() {
		return {
			fields,
			adminLinkCopied: false,
			publicLinkCopied: false,
			showTalkModal: false,
		}
	},

	computed: {
		publicLink() {
			return this.excalidrawUrl + '/#room=' + this.board.id + ',' + this.board.key
		},
		fieldsToDisplay() {
			const result = {}
			Object.keys(this.fields).forEach((fieldId) => {
				const field = this.fields[fieldId]
				// do not display password if not set
				if (!['password'].includes(field.type) || this.board[fieldId]) {
					result[fieldId] = field
				}
			})
			return result
		},
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		async copyLink() {
			const link = this.publicLink
			try {
				await this.$copyText(link)
				this.publicLinkCopied = true
				showSuccess(t('integration_excalidraw', 'Public link copied!'))
				// eslint-disable-next-line
				new Timer(() => {
					this.publicLinkCopied = false
				}, 5000)
			} catch (error) {
				console.error(error)
				showError(t('integration_excalidraw', 'Link could not be copied to clipboard'))
			}
		},
		getFormattedDate(date) {
			return moment(date).format('LL')
		},
		getFormattedDatetime(date) {
			return moment(date).format('LLL')
		},
		discify(string) {
			return '•'.repeat(string.length)
		},
	},
}
</script>

<style scoped lang="scss">
.details-wrapper {
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
}

.boardDetails {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	// background-color: var(--color-primary-element-lighter);
	// background-color: var(--color-primary-light);
	box-shadow: 0 0 10px var(--color-box-shadow);
	border-radius: var(--border-radius-large);
	h2 {
		margin: 12px 0 32px 0;
	}
	.fields {
		display: flex;
		flex-direction: column;
		.field {
			display: flex;
			align-items: center;
			margin: 8px 0;
			padding: 8px;
			border-radius: var(--border-radius);
			&:hover {
				background-color: var(--color-background-hover);
			}
			> * {
				margin: 0 8px 0 8px;
			}
			.emptyIcon {
				width: 20px;
			}
			.fieldLabel {
				width: 250px;
			}
			> .fieldValue {
				width: 300px;
				&.multiple {
					display: flex;
					> * {
						margin-right: 8px;
					}
				}
				&.multipleVertical {
					display: flex;
					flex-direction: column;
					.oneValue {
						display: flex;
						> * {
							margin-right: 8px;
						}
					}
				}
			}
			textarea.fieldValue {
				width: 300px;
				height: 65px;
				resize: none;
			}
			.colorDot {
				width: 24px;
				height: 24px;
				border-radius: 50%;
			}
			.password-input-wrapper {
				display: flex;
				align-items: center;
				width: 300px;
				label {
					flex-grow: 1;
				}
			}
		}
	}
	.links {
		display: flex;
		flex-direction: column;
		.link {
			display: flex;
			align-items: center;
			margin: 6px 0 6px 0;
			> * {
				margin: 0 8px 0 8px;
			}
			label {
				width: 250px;
			}
			.linkInputWrapper {
				display: flex;
				width: 300px;
				input {
					flex-grow: 1;
				}
				a {
					margin-left: 8px;
				}
			}
			.copiedIcon {
				color: var(--color-success);
			}
		}
		.talk-button-wrapper {
			display: flex;
			justify-content: center;
		}
	}
}
</style>
