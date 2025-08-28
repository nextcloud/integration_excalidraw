<template>
	<NcModal
		class="send-modal"
		size="small"
		@close="$emit('close')">
		<div class="modal-content">
			<h2>
				<TalkIcon class="icon" />
				{{ t('integration_excalidraw', 'Send link to a Talk room') }}
			</h2>
			<div class="input-wrapper">
				<input ref="query"
					v-model="query"
					type="text"
					:placeholder="t('integration_excalidraw', 'Search for users, groups or conversations')"
					@input="searchQueryChanged">
				<CloseIcon v-show="query" @click="resetQuery" />
			</div>
			<div class="results">
				<div v-show="conversationsToShow.length > 0" id="conversations">
					<h3>
						{{ t('integration_excalidraw', 'Conversations') }}
					</h3>
					<ul>
						<NcListItem v-for="c in conversationsToShow"
							:key="'conv-' + c.id"
							:name="c.displayName"
							:active="selectedRoom && selectedRoom.id === c.id"
							:bold="selectedRoom && selectedRoom.id === c.id"
							@click="selectedRoom = c">
							<template #subtitle>
								{{ c.description }}
							</template>
							<template #icon>
								<NcAvatar v-if="c.type === CONVERSATION_TYPE.ONE_TO_ONE"
									:size="34"
									:user="c.name"
									:disable-menu="true" />
								<NcAvatar v-else
									:size="34"
									icon-class="icon-group" />
							</template>
						</NcListItem>
					</ul>
				</div>
				<div v-show="usersToShow.length > 0" id="users">
					<h3>
						{{ t('integration_excalidraw', 'Users') }}
					</h3>
					<ul>
						<NcListItem v-for="c in usersToShow"
							:key="'user-' + c.id"
							:name="c.displayName"
							:active="selectedRoom && selectedRoom.id === c.id"
							:bold="selectedRoom && selectedRoom.id === c.id"
							@click="selectedRoom = c">
							<template #icon>
								<NcAvatar
									:size="34"
									:user="c.name"
									:disable-menu="true" />
							</template>
						</NcListItem>
					</ul>
				</div>
				<div v-show="groupsToShow.length > 0" id="groups">
					<h3>
						{{ t('integration_excalidraw', 'Groups') }}
					</h3>
					<ul>
						<NcListItem v-for="c in groupsToShow"
							:key="'group-' + c.id"
							:title="c.displayName"
							:active="selectedRoom && selectedRoom.id === c.id"
							:bold="selectedRoom && selectedRoom.id === c.id"
							@click="selectedRoom = c">
							<template #icon>
								<NcAvatar
									:size="34"
									icon-class="icon-group" />
							</template>
						</NcListItem>
					</ul>
				</div>
			</div>
			<div class="spacer" />
			<div class="modal-footer">
				<div class="spacer" />
				<NcButton @click="$emit('close')">
					{{ t('integration_excalidraw', 'Cancel') }}
				</NcButton>
				<NcButton type="primary"
					:disabled="selectedRoom === null"
					@click="onSendLinkClick">
					<template #icon>
						<SendOutlineIcon :class="{ 'icon-loading': sending }" />
					</template>
					{{ t('integration_excalidraw', 'Send') }}
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>
import CloseIcon from 'vue-material-design-icons/Close.vue'
import SendOutlineIcon from 'vue-material-design-icons/SendOutline.vue'

import TalkIcon from './TalkIcon.vue'

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcAvatar from '@nextcloud/vue/dist/Components/NcAvatar.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcListItem from '@nextcloud/vue/dist/Components/NcListItem.js'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { generateOcsUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { delay } from '../../utils.js'

const CONVERSATION_TYPE = {
	ONE_TO_ONE: 1,
	GROUP: 2,
	PUBLIC: 3,
	CHANGELOG: 4,
}

export default {
	name: 'SendModal',

	components: {
		NcButton,
		NcAvatar,
		NcModal,
		SendOutlineIcon,
		CloseIcon,
		NcListItem,
		TalkIcon,
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
	},

	data() {
		return {
			CONVERSATION_TYPE,
			opened: false,
			myRooms: [],
			openRooms: [],
			usersAndGroups: [],
			loadingOpenRooms: false,
			loadingUsersAndGroups: false,
			selectedRoom: null,
			sending: false,
			query: '',
		}
	},

	computed: {
		publicLink() {
			return this.excalidrawUrl + '/#room=' + this.board.id + ',' + this.board.key
		},
		conversationsToShow() {
			return [
				...this.myRooms.filter((r) => r.name.includes(this.query) || r.displayName.includes(this.query)),
				...this.openRooms,
			]
		},
		usersToShow() {
			return [
				...this.usersAndGroups.filter((item) => {
					// avoid users for which we already have a 1-1 conversation
					return item.type === CONVERSATION_TYPE.ONE_TO_ONE
						&& !this.myRooms.find((r) => r.type === CONVERSATION_TYPE.ONE_TO_ONE && item.id === r.name)
				}),
			]
		},
		groupsToShow() {
			return [
				...this.usersAndGroups.filter((item) => {
					return item.type === CONVERSATION_TYPE.GROUP
				}),
			]
		},
	},

	watch: {
	},

	beforeMount() {
		this.getMyRooms()
	},

	methods: {
		getMyRooms() {
			const url = generateOcsUrl('/apps/spreed/api/v4/room')
			axios.get(url).then((response) => {
				this.myRooms = response.data.ocs.data.filter((r) => r.readOnly === 0)
				console.debug('TALK rooms response', response.data.ocs.data)
			}).catch((error) => {
				console.debug(error)
			})
		},
		searchQueryChanged(event) {
			this.query = event.target.value
			this.selectedRoom = null
			if (this.query === '') {
				this.usersAndGroups = []
				this.openRooms = []
			} else {
				delay(() => {
					this.search()
				}, 400)()
			}
		},
		search() {
			const allPromises = [
				this.findOpenRooms(this.query),
				this.findUsersAndGroups(this.query),
			]
			Promise.all(allPromises)
				.catch(error => {
					console.error(error)
					showError(error?.response?.data?.error || error.message)
				})
				.then(() => {
					console.debug('allPromises finished', allPromises)
					this.loadingUsersAndGroups = false
					this.loadingOpenRooms = false

					// open rooms
					allPromises[0].then((response) => {
						this.openRooms = response.data.ocs.data
							.filter((r) => r.readOnly === 0)
							.map((r) => { return { ...r, isOpenRoom: true } })
						console.debug('TALK OPEN rooms response', response.data.ocs.data)
						console.debug('findOpenRooms finished')
					}).catch((error) => {
						console.error(error)
					})

					// users and groups
					allPromises[1].then((response) => {
						this.usersAndGroups = response.data.ocs.data.map((u) => {
							return {
								id: u.id,
								name: u.id,
								displayName: u.label,
								type: u.source === 'users' ? CONVERSATION_TYPE.ONE_TO_ONE : CONVERSATION_TYPE.GROUP,
								exists: false,
							}
						})
						console.debug('findUsersAndGroups finished')
					}).catch((error) => {
						console.error(error)
					})
				})
		},
		resetQuery() {
			this.query = ''
			this.selectedRoom = null
			this.usersAndGroups = []
			this.openRooms = []
			this.$refs.query.focus()
		},
		findUsersAndGroups(query) {
			if (query === '') {
				this.usersAndGroups = []
				this.selectedRoom = null
				return
			}
			this.loadingUsersAndGroups = true
			const url = generateOcsUrl('core/autocomplete/get', 2).replace(/\/$/, '')
			return axios.get(url, {
				params: {
					format: 'json',
					search: query,
					itemType: ' ',
					itemId: ' ',
					shareTypes: [0, 1],
				},
			})
		},
		findOpenRooms(query) {
			if (query === '') {
				this.openRooms = []
				this.selectedRoom = null
				return
			}
			this.loadingOpenRooms = true
			const url = generateOcsUrl('/apps/spreed/api/v4/listed-room')
			const req = {
				params: {
					searchTerm: query,
				},
			}
			return axios.get(url, req)
		},
		onSendLinkClick() {
			if (this.selectedRoom.isOpenRoom) {
				this.joinOpenRoom()
			} else if (this.selectedRoom.exists === false) {
				this.createRoom()
			} else {
				this.sendLink()
			}
		},
		createRoom() {
			this.sending = true
			const url = generateOcsUrl('/apps/spreed/api/v4/room')
			const req = {
				roomType: this.selectedRoom.type,
				invite: this.selectedRoom.id,
			}

			axios.post(url, req).then((response) => {
				console.debug('TALK create room response', response.data.ocs.data)
				showSuccess(t('integration_excalidraw', 'You created a conversation with {name}', { name: this.selectedRoom.displayName }))
				this.selectedRoom.token = response.data.ocs.data.token
				this.sendLink()
			}).catch((error) => {
				showError(t('integration_excalidraw', 'Failed to join')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? ''))
				console.debug(error)
				this.sending = false
			})
		},
		joinOpenRoom() {
			this.sending = true
			const token = this.selectedRoom.token
			const url = generateOcsUrl('/apps/spreed/api/v4/room/{token}/participants/active', { token })
			const req = {
				force: false,
			}

			axios.post(url, req).then((response) => {
				console.debug('TALK response', response)
				showSuccess(t('integration_excalidraw', 'You joined {name}', { name: this.selectedRoom.displayName }))
				this.sendLink()
			}).catch((error) => {
				showError(t('integration_excalidraw', 'Failed to join')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? ''))
				console.debug(error)
				this.sending = false
			})
		},
		sendLink() {
			const token = this.selectedRoom.token
			const url = generateOcsUrl('/apps/spreed/api/v1/chat/{token}', { token })
			const req = {
				message: t('integration_excalidraw', 'Excalidraw room "{name}": {link}', {
					link: this.publicLink,
					name: this.board.name,
				}),
			}

			axios.post(url, req).then((response) => {
				console.debug('TALK response', response)
				showSuccess(t('integration_excalidraw', 'Link sent to {name}', { name: this.selectedRoom.displayName }))
				this.$emit('close')
			}).catch((error) => {
				showError(t('integration_excalidraw', 'Failed to send link')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? ''))
				console.debug(error)
			}).then(() => {
				this.sending = false
			})
		},
	},
}
</script>

<style scoped lang="scss">
:deep(.modal-container) {
	min-height: 90%;
	display: flex !important;
	flex-direction: column;
}

.modal-content {
	flex-grow: 1;
	padding: 12px;
	min-height: 300px;
	height: 100%;
	display: flex;
	flex-direction: column;

	h2 {
		display: flex;
		align-items: center;
		justify-content: center;
		.icon {
			margin-right: 8px;
		}
	}

	h3 {
		font-weight: bold;
		color: var(--color-primary-element);
	}

	.input-wrapper {
		display: flex;
		align-items: center;
		input {
			flex-grow: 1;
		}
	}

	.spacer {
		flex-grow: 1;
	}

	.multi-select {
		width: 100%;
		.option-label {
			margin-left: 8px;
		}
	}

	.modal-footer {
		width: 100%;
		display: flex;
		align-items: center;
		margin-top: 12px;

		> * {
			margin-left: 8px;
		}
	}

	#conversations {
		padding-right: 16px;
	}
}
</style>
