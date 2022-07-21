import Vue from 'vue'
import './bootstrap'
import ExcalidrawModalWrapper from './components/ExcalidrawModalWrapper'

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

console.debug('!!! Excalidraw standalone modal is ready')
