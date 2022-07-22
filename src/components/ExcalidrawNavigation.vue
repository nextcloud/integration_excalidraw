<template>
	<AppNavigation>
		<template #list>
			<AppNavigationNew v-if="isConfigured"
				:text="t('integration_excalidraw', 'Create a room')"
				button-class="icon-add"
				@click="onCreateBoardClick" />
			<BoardNavigationItem v-for="board in boards"
				:key="board.id"
				class="boardItem"
				:board="board"
				:selected="board.id === selectedBoardId"
				@board-clicked="onBoardClicked"
				@delete-board="onBoardDeleted" />
		</template>
	</AppNavigation>
</template>

<script>
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import BoardNavigationItem from './BoardNavigationItem'

export default {
	name: 'ExcalidrawNavigation',

	components: {
		BoardNavigationItem,
		AppNavigationNew,
		AppNavigation,
	},

	props: {
		boards: {
			type: Array,
			required: true,
		},
		selectedBoardId: {
			type: String,
			required: true,
		},
		isConfigured: {
			type: Boolean,
			required: true,
		},
	},

	data() {
		return {
		}
	},

	computed: {
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		onCreateBoardClick() {
			this.$emit('create-board-clicked')
		},
		onBoardClicked(boardId) {
			this.$emit('board-clicked', boardId)
		},
		onBoardDeleted(boardId) {
			this.$emit('delete-board', boardId)
		},
	},
}
</script>

<style scoped lang="scss">
.addBoardItem {
	border-bottom: 1px solid var(--color-border);
}

:deep(.boardItem) {
	padding-right: 0 !important;
	&.selectedBoard {
		> a,
		> div {
			background: var(--color-primary-light, lightgrey);
		}

		> a {
			font-weight: bold;
		}
	}
}
</style>
