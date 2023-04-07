const HtmxAlpineTailwindDemos = {
	initHTMXXRequestedWithXMLHttpRequest: function () {
		document.body.addEventListener("htmx:configRequest", (event) => {
			const csrf_token = HtmxAlpineTailwindDemos.getCSRFToken()
			event.detail.headers[csrf_token.name] = csrf_token.value
			// add XMLHttpRequest to header to work with $config->ajax
			event.detail.headers["X-Requested-With"] = "XMLHttpRequest"
		})
	},

	listenToHTMXRequests: function () {
		// before send
		htmx.on("htmx:beforeSend", function (event) {
			debugLogger(
				`HtmxAlpineTailwindDemos - listenToHTMXRequests - beforeSend - event: ${event}`
			)
		})

		// after swap
		htmx.on("htmx:afterSwap", function (event) {
			debugLogger(
				`HtmxAlpineTailwindDemos - listenToHTMXRequests - afterSwap - event: ${event}`
			)
		})

		// after settle
		// @note: aftersettle is fired AFTER  afterswap
		// @todo: maybe even use css to transition in so user doesn't 'perceive' a delay?
		htmx.on("htmx:afterSettle", function (event) {
			debugLogger(`HtmxAlpineTailwindDemos - listenToHTMXRequests - 		htmx.on("htmx:afterSettle", function (event) {
				- event: ${event}`)
		})
	},
	getCSRFToken: function () {
		// @TODO ADD TO main.php
		// find hidden input with id 'csrf-token'
		const tokenInput = htmx.find("._post_token")
		return tokenInput
	},

	handleSomethingForHTMX: function () {},
	sendCustomEventToAlpineJS: function () {
		// @TODO
	},
}

// ~~~~~~~~~~~~~~~~~~

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	if (typeof htmx !== "undefined") {
		// console.log("INIT HTMX")
		// init htmx header
		HtmxAlpineTailwindDemos.initHTMXXRequestedWithXMLHttpRequest()
		// init listen to htmx requests
		HtmxAlpineTailwindDemos.listenToHTMXRequests()
	}
})

// ################
// DEBUG
function debugLogger(message) {
	const funcName = debugLogger.caller.name
	console.log(`${funcName}: ${message}`)
	// console.info(`${funcName}: ${message}`)
	// console.error(`${funcName}: ${message}`)
	// console.warn(`${funcName}: ${message}`)
}

// ################

// ALPINE
document.addEventListener("alpine:init", () => {
	Alpine.store("HtmxAlpineTailwindDemosStore", {
		// GLOBAL
		daisyui: {
			themes: [
				"light",
				"dark",
				"cupcake",
				"bumblebee",
				"emerald",
				"corporate",
				"synthwave",
				"retro",
				"cyberpunk",
				"valentine",
				"halloween",
				"garden",
				"forest",
				"aqua",
				"lofi",
				"pastel",
				"fantasy",
				"wireframe",
				"black",
				"luxury",
				"dracula",
				"cmyk",
				"autumn",
				"business",
				"acid",
				"lemonade",
				"night",
				"coffee",
				"winter",
			],
		},
		current_theme: "cyberpunk",
		// PROPERTIES
		//----------------

		is_modal_open: false,
		/* product attributes */
		product_attributes: [],
		/* product attributes options */
		attributes_options: [],
		// main product
		main_product: {},
		// all product variants
		product_variants: [],
		// ************
	})

	Alpine.data("HtmxAlpineTailwindDemosData", () => ({
		//---------------
		// FUNCTIONS
		// GLOBAL
		getDaisyUIThemes() {
			const daisUIThemes = this.getStoreValue("daisyui")
			return daisUIThemes.themes
		},

		getCurrentTheme() {
			const currentTheme = this.getStoreValue("current_theme")
			return currentTheme
		},

		// #########

		/**
		 * Get the value of a given store property.
		 * @param string property Property in store whose value to return
		 * @returns {any}
		 */
		getStoreValue(property) {
			return this.$store.HtmxAlpineTailwindDemosStore[property]
		},

		/**
		 * Set a store property value.
		 * @param any value Value to set in store.
		 * @return {void}.
		 */
		setStoreValue(property, value) {
			debugLogger(`PROPERTY: ${property}`)
			debugLogger(`VALUE: ${value}`)
			this.$store.HtmxAlpineTailwindDemosStore[property] = value
		},

		// -------

		handleBuyNow(product_id) {
			debugLogger(`BUY NOW PRODUCT ID: ${product_id}`)
		},

		handleSomeAction() {
			const message = "htmx Alpine.JS and Tailwind CSS are awesome!"
			// -----
			this.handleAnotherAction(message)
		},
		handleAnotherAction(message) {
			debugLogger(message)
		},

		sendCustomEventToHTMX() {
			// @TODO
		},
	}))
})
