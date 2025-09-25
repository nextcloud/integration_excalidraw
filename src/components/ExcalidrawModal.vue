<template>
	<NcModal
		size="full"
		:close-button-contained="false"
		@close="$emit('close')">
		<iframe
			class="frame"
			frameborder="0"
			:allowFullScreen="true"
			:src="boardUrl" />
	</NcModal>
</template>

<script>
import NcModal from '@nextcloud/vue/components/NcModal'

export default {
	name: 'ExcalidrawModal',

	components: {
		NcModal,
	},

	props: {
		boardUrl: {
			type: String,
			required: true,
		},
	},

	emits: ['close'],

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
