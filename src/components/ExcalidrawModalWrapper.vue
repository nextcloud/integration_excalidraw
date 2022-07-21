<template>
	<div id="excalidrawModalWrapper">
		<ExcalidrawModal v-if="open && roomUrl"
			:board-url="roomUrl"
			@close="onClose" />
	</div>
</template>

<script>
import ExcalidrawModal from './ExcalidrawModal'

export default {
	name: 'ExcalidrawModalWrapper',

	components: {
		ExcalidrawModal,
	},

	props: {
	},

	data() {
		return {
			roomUrl: null,
			open: false,
		}
	},

	computed: {
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		openOn(roomUrl) {
			this.roomUrl = roomUrl
			this.open = true
		},
		onClose() {
			this.open = false
			this.$emit('close')
		},
	},
}
</script>

<style scoped lang="scss">
// nothing yet
</style>
