<template>
	<NcAppNavigation>
		<template #list>
			<NcAppNavigationNew v-if="isConfigured"
				:text="t('integration_excalidraw', 'Create a room')"
				@click="onCreateBoardClick">
				<template #icon>
					<PlusIcon />
				</template>
			</NcAppNavigationNew>
			<BoardNavigationItem v-for="board in boards"
				:key="board.id"
				class="boardItem"
				:board="board"
				:selected="board.id === selectedBoardId"
				@board-clicked="onBoardClicked"
				@delete-board="onBoardDeleted" />
		</template>
	</NcAppNavigation>
</template>

<script>
import PlusIcon from 'vue-material-design-icons/Plus.vue'

import NcAppNavigationNew from '@nextcloud/vue/components/NcAppNavigationNew'
import NcAppNavigation from '@nextcloud/vue/components/NcAppNavigation'
import BoardNavigationItem from './BoardNavigationItem.vue'

export default {
	name: 'ExcalidrawNavigation',

	components: {
		BoardNavigationItem,
		NcAppNavigationNew,
		NcAppNavigation,
		PlusIcon,
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

	emits: ['create-board-clicked', 'board-clicked', 'delete-board'],

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
