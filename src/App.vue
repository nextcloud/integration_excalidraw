<template>
	<Content app-name="integration_excalidraw">
		<ExcalidrawNavigation
			:boards="activeBoards"
			:selected-board-id="selectedBoardId"
			:is-configured="connected"
			@create-board-clicked="onCreateBoardClick"
			@board-clicked="onBoardClicked"
			@delete-board="onBoardDeleted" />
		<AppContent
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
			<EmptyContent v-else-if="activeBoardCount === 0">
				<template #icon>
					<ExcalidrawIcon />
				</template>
				<span class="emptyContentWrapper">
					<span>
						{{ t('integration_excalidraw', 'You haven\'t created any session yet') }}
					</span>
					<Button
						class="createButton"
						@click="onCreateBoardClick">
						<template #icon>
							<PlusIcon />
						</template>
						{{ t('integration_excalidraw', 'Create a session') }}
					</Button>
				</span>
			</EmptyContent>
			<EmptyContent v-else>
				<template #icon>
					<ExcalidrawIcon />
				</template>
				{{ t('integration_excalidraw', 'No selected session') }}
			</EmptyContent>
		</AppContent>
		<Modal v-if="creationModalOpen"
			size="small"
			@close="closeCreationModal">
			<CreationForm
				:loading="creating"
				@ok-clicked="onCreationValidate"
				@cancel-clicked="closeCreationModal" />
		</Modal>
	</Content>
</template>

<script>
import PlusIcon from 'vue-material-design-icons/Plus'
import Button from '@nextcloud/vue/dist/Components/Button'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Content from '@nextcloud/vue/dist/Components/Content'
import Modal from '@nextcloud/vue/dist/Components/Modal'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import axios from '@nextcloud/axios'
import { showSuccess, showError, showUndo } from '@nextcloud/dialogs'

import ExcalidrawNavigation from './components/ExcalidrawNavigation'
import CreationForm from './components/CreationForm'
import BoardDetails from './components/BoardDetails'
import ExcalidrawIcon from './components/icons/ExcalidrawIcon'
import { Timer } from './utils'

export default {
	name: 'App',

	components: {
		ExcalidrawIcon,
		CreationForm,
		BoardDetails,
		ExcalidrawNavigation,
		PlusIcon,
		AppContent,
		Content,
		Modal,
		EmptyContent,
		Button,
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
				showSuccess(t('integration_excalidraw', 'New session was created in Excalidraw'))
				board.id = response.data?.id
				board.key = response.data?.key
				board.trash = false
				this.state.board_list.push(board)
				this.selectedBoardId = board.id
				this.creationModalOpen = false
			}).catch((error) => {
				showError(
					t('integration_excalidraw', 'Failed to create new session')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? '')
				)
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
				showError(
					t('integration_excalidraw', 'Failed to delete the session')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? '')
				)
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
				{ timeout: 10000 }
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

.settings {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.emptyContentWrapper {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.createButton,
.configureButton {
	margin-top: 12px;
}
</style>
