import Vue from 'vue'
import './bootstrap.js'
import { loadState } from '@nextcloud/initial-state'
import ExcalidrawModalWrapper from './components/ExcalidrawModalWrapper.vue'

function init() {
	if (!OCA.Excalidraw) {
		/**
		 * @namespace
		 */
		OCA.Excalidraw = {}
	}

	const wrapperId = 'excalidrawModalWrapper'
	const wrapperElement = document.createElement('div')
	wrapperElement.id = wrapperId
	document.body.append(wrapperElement)

	const View = Vue.extend(ExcalidrawModalWrapper)
	OCA.Excalidraw.ExcalidrawModalWrapperVue = new View().$mount('#' + wrapperId)

	OCA.Excalidraw.openModal = (roomUrl) => {
		OCA.Excalidraw.ExcalidrawModalWrapperVue.openOn(roomUrl)
	}
}

function listen(baseUrl) {
	const body = document.querySelector('body')
	body.addEventListener('click', (e) => {
		const link = (e.target.tagName === 'A')
			? e.target
			: (e.target.parentElement?.tagName === 'A')
				? e.target.parentElement
				: null
		if (link !== null) {
			const href = link.getAttribute('href')
			if (!href) {
				return
			}
			if (href.startsWith(baseUrl + '/')) {
				e.preventDefault()
				e.stopPropagation()
				OCA.Excalidraw.openModal(href)
			}
		}
	})
}

const baseUrl = loadState('integration_excalidraw', 'base_url')
const overrideLinkClick = loadState('integration_excalidraw', 'override_link_click')
if (baseUrl) {
	init()
	console.debug('!!! Excalidraw standalone modal is ready', baseUrl)
	if (overrideLinkClick) {
		console.debug('Excalidraw will handle clicks on links')
		listen(baseUrl)
	}
} else {
	console.debug('!!! Excalidraw standalone: disabled')
}
