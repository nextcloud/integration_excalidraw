<template>
	<AppNavigationItem
		:title="board.name"
		:class="{ selectedBoard: selected }"
		:force-menu="true"
		@click="onBoardClick">
		<template #icon>
			<ClipboardEditIcon v-if="selected"
				:size="20" />
			<ClipboardEditOutlineIcon v-else
				:size="20" />
		</template>
		<template #actions>
			<ActionButton
				:close-after-click="true"
				@click="onDeleteClick">
				<template #icon>
					<DeleteIcon :size="20" />
				</template>
				{{ t('integration_excalidraw', 'Delete') }}
			</ActionButton>
		</template>
	</AppNavigationItem>
</template>

<script>
import DeleteIcon from 'vue-material-design-icons/Delete.vue'
import ClipboardEditIcon from 'vue-material-design-icons/ClipboardEdit.vue'
import ClipboardEditOutlineIcon from 'vue-material-design-icons/ClipboardEditOutline.vue'
import ClickOutside from 'vue-click-outside'

import ActionButton from '@nextcloud/vue/dist/Components/ActionButton.js'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem.js'

export default {
	name: 'BoardNavigationItem',
	components: {
		AppNavigationItem,
		ActionButton,
		ClipboardEditIcon,
		ClipboardEditOutlineIcon,
		DeleteIcon,
	},
	directives: {
		ClickOutside,
	},
	props: {
		board: {
			type: Object,
			required: true,
		},
		selected: {
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
	beforeMount() {
	},
	methods: {
		onBoardClick(e) {
			this.$emit('board-clicked', this.board.id)
		},
		onDeleteClick() {
			this.$emit('delete-board', this.board.id)
		},
	},
}
</script>

<style scoped lang="scss">
// nothing
</style>
