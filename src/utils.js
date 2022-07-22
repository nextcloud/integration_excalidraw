import TextIcon from 'vue-material-design-icons/Text'

export function Timer(callback, mydelay) {
	let timerId
	let start
	let remaining = mydelay

	this.pause = function() {
		window.clearTimeout(timerId)
		remaining -= new Date() - start
	}

	this.resume = function() {
		start = new Date()
		window.clearTimeout(timerId)
		timerId = window.setTimeout(callback, remaining)
	}

	this.resume()
}

let mytimer = 0
export function delay(callback, ms) {
	return function() {
		const context = this
		const args = arguments
		clearTimeout(mytimer)
		mytimer = setTimeout(function() {
			callback.apply(context, args)
		}, ms || 0)
	}
}

export const fields = {
	name: {
		icon: TextIcon,
		label: t('integration_excalidraw', 'Room name'),
		type: 'text',
		placeholder: t('integration_excalidraw', 'Room name'),
		default: t('integration_excalidraw', 'New room'),
		mandatory: true,
	},
}
