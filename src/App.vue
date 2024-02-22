<template>
	<NcContent app-name="integration_excalidraw">
		<ExcalidrawNavigation
			:boards="activeBoards"
			:selected-board-id="selectedBoardId"
			:is-configured="connected"
			@create-board-clicked="onCreateBoardClick"
			@board-clicked="onBoardClicked"
			@delete-board="onBoardDeleted" />
		<NcAppContent
			:list-max-width="50"
			:list-min-width="20"
			:list-size="20"
			:show-details="false"
			@update:showDetails="a = 2">
			<!--template slot="list">
			</template-->
			<BoardDetails v-if="selectedBoard"
				:board="selectedBoard"
				:excalidraw-url="state.base_url"
				:talk-enabled="state.talk_enabled" />
			<NcEmptyContent v-else-if="activeBoardCount === 0"
				:name="t('integration_excalidraw', 'You haven\'t created any room yet')">
				<template #icon>
					<ExcalidrawIcon />
				</template>
				<template #action>
					<NcButton
						class="createButton"
						@click="onCreateBoardClick">
						<template #icon>
							<PlusIcon />
						</template>
						{{ t('integration_excalidraw', 'Create a room') }}
					</NcButton>
				</template>
			</NcEmptyContent>
			<NcEmptyContent v-else
				:name="t('integration_excalidraw', 'No selected room')">
				<template #icon>
					<ExcalidrawIcon />
				</template>
			</NcEmptyContent>
		</NcAppContent>
		<NcModal v-if="creationModalOpen"
			size="small"
			@close="closeCreationModal">
			<CreationForm
				:loading="creating"
				focus-on-field="name"
				@ok-clicked="onCreationValidate"
				@cancel-clicked="closeCreationModal" />
		</NcModal>
	</NcContent>
</template>

<script>
import PlusIcon from 'vue-material-design-icons/Plus.vue'

import ExcalidrawIcon from './components/icons/ExcalidrawIcon.vue'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'

import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import axios from '@nextcloud/axios'
import { showSuccess, showError, showUndo } from '@nextcloud/dialogs'

import ExcalidrawNavigation from './components/ExcalidrawNavigation.vue'
import CreationForm from './components/CreationForm.vue'
import BoardDetails from './components/BoardDetails.vue'
import { Timer } from './utils.js'

export default {
	name: 'App',

	components: {
		ExcalidrawIcon,
		CreationForm,
		BoardDetails,
		ExcalidrawNavigation,
		PlusIcon,
		NcAppContent,
		NcContent,
		NcModal,
		NcEmptyContent,
		NcButton,
	},

	props: {
	},

	data() {
		return {
			creationModalOpen: false,
			selectedBoardId: '',
			state: loadState('integration_excalidraw', 'excalidraw-state'),
			configureUrl: generateUrl('/settings/user/connected-accounts'),
			creating: false,
		}
	},

	computed: {
		connected() {
			return !!this.state.base_url
		},
		activeBoards() {
			return this.state.board_list.filter((b) => !b.trash)
		},
		activeBoardsById() {
			return this.activeBoards.reduce((object, item) => {
				object[item.id] = item
				return object
			}, {})
		},
		activeBoardCount() {
			return this.activeBoards.length
		},
		selectedBoard() {
			return this.selectedBoardId
				? this.activeBoardsById[this.selectedBoardId]
				: null
		},
	},

	watch: {
	},

	beforeMount() {
		console.debug('state', this.state)
	},

	mounted() {
	},

	methods: {
		onCreateBoardClick() {
			this.creationModalOpen = true
		},
		closeCreationModal() {
			this.creationModalOpen = false
		},
		onCreationValidate(board) {
			this.creating = true
			const req = {
				name: board.name,
			}
			const url = generateUrl('/apps/integration_excalidraw/board')
			axios.post(url, req).then((response) => {
				showSuccess(t('integration_excalidraw', 'New room was created'))
				board.id = response.data?.id
				board.key = response.data?.key
				board.trash = false
				this.state.board_list.push(board)
				this.selectedBoardId = board.id
				this.creationModalOpen = false
			}).catch((error) => {
				showError(t('integration_excalidraw', 'Failed to create new room')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? ''))
				console.debug(error)
			}).then(() => {
				this.creating = false
			})
		},
		onBoardClicked(boardId) {
			console.debug('select board', boardId)
			this.selectedBoardId = boardId
		},
		deleteBoard(boardId) {
			console.debug('DELETE board', boardId)
			const url = generateUrl('/apps/integration_excalidraw/board/{boardId}', { boardId })
			axios.delete(url).then((response) => {
			}).catch((error) => {
				showError(t('integration_excalidraw', 'Failed to delete the room')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? ''))
				console.debug(error)
			})
		},
		onBoardDeleted(boardId) {
			// deselect the board
			if (boardId === this.selectedBoardId) {
				this.selectedBoardId = ''
			}

			// hide the board nav item
			const boardIndex = this.state.board_list.findIndex((b) => b.id === boardId)
			const board = this.state.board_list[boardIndex]
			if (boardIndex !== -1) {
				board.trash = true
			}

			// cancel or delete
			const deletionTimer = new Timer(() => {
				this.deleteBoard(boardId)
			}, 10000)
			showUndo(
				t('integration_excalidraw', '{name} deleted', { name: board.name }),
				() => {
					deletionTimer.pause()
					board.trash = false
				},
				{ timeout: 10000 },
			)
		},
	},
}
</script>

<style scoped lang="scss">
// TODO in global css loaded by main
body {
	min-height: 100%;
	height: auto;
}

.empty-content {
	margin: 20px;
}

.settings {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.createButton,
.configureButton {
	margin-top: 12px;
}
</style>
