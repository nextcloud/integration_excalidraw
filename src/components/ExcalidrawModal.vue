<template>
	<Modal
		size="full"
		@close="$emit('close')">
		<iframe
			class="frame"
			frameborder="0"
			:allowFullScreen="true"
			:src="boardUrl" />
	</Modal>
</template>

<script>
import Modal from '@nextcloud/vue/dist/Components/Modal'

export default {
	name: 'ExcalidrawModal',

	components: {
		Modal,
	},

	props: {
		boardUrl: {
			type: String,
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
	},
}
</script>

<style scoped lang="scss">
.frame {
	width: 100%;
	height: 100%;
}
</style>
