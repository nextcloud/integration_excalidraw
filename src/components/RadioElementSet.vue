<template>
	<span class="option-list">
		<RadioElement v-for="(option, optionId) in options"
			:key="optionId"
			:checked="value"
			:value="optionId"
			:name="name"
			:border-radius="borderRadius"
			@update:checked="onUpdateValue">
			<template v-if="$scopedSlots.icon || option.icon" #icon>
				<slot name="icon" :option="option">
					<component :is="option.icon"
						v-if="option.icon" />
				</slot>
			</template>
			<slot name="label" :option="option">
				{{ option.label }}
			</slot>
		</RadioElement>
	</span>
</template>

<script>
import RadioElement from './RadioElement.vue'

export default {
	name: 'RadioElementSet',
	components: { RadioElement },
	props: {
		options: {
			type: Object,
			required: true,
		},
		value: {
			type: String,
			required: true,
		},
		// to make sure the sub ids are unique
		name: {
			type: String,
			required: true,
		},
		borderRadius: {
			type: Number,
			default: undefined,
		},
	},

	computed: {
	},

	methods: {
		onUpdateValue(newValue) {
			this.$emit('update:value', newValue)
		},
	},
}
</script>

<style scoped lang="scss">
.option-list {
	display: flex;
	flex-direction: column;
	margin: 44px 0 44px 0;
}
</style>
