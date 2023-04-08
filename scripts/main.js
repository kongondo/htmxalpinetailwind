const HtmxAlpineTailwindDemos = {
	initHTMXXRequestedWithXMLHttpRequest: function () {
		document.body.addEventListener("htmx:configRequest", (event) => {
			const csrf_token = HtmxAlpineTailwindDemos.getCSRFToken()
			event.detail.headers[csrf_token.name] = csrf_token.value
			debugLogger(`headers: ${event.detail.headers}`)
			console.log("headers", event.detail.headers)
			console.log("parameters", event.detail.parameters)
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
		// @TODO DELETE IF NOT IN USE
		// init() {

		// },
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
		// current_buy_now_product_id: 0,
		current_buy_now_product_values: {},
		current_buy_now_product_quantity: 1,
		current_buy_now_product_total_price: 0,
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
			// debugLogger(`PROPERTY: ${property}`)
			// debugLogger(`VALUE: ${value}`)
			this.$store.HtmxAlpineTailwindDemosStore[property] = value
		},

		// -------

		handleBuyNow(product_values) {
			// ---------

			// @NOTE/@TODO HERE YOU SHOULD HANDLE ERRORS, ensure expected properties are in the sent object, etc!
			const isModalOpenProperty = "is_modal_open"
			const currentIsModalOpenValue = this.getStoreValue(isModalOpenProperty)
			const incomingIsModalOpenValue = !currentIsModalOpenValue

			// set current buy now product id to the store
			// this.setCurrentBuyNowProductID(currentBuyNowProductID)
			this.setCurrentBuyNowProductValues(product_values)

			// --------
			// open or close modal for buy now
			this.setStoreValue(isModalOpenProperty, incomingIsModalOpenValue)
		},

		handleBuyNowQuantity(amount) {
			const currentBuyNowProductValues = this.getStoreValue(
				"current_buy_now_product_values"
			)

			const currentBuyNowProductID = parseInt(
				currentBuyNowProductValues.product_id
			)
			if (!currentBuyNowProductID) {
				// return early; no product ID; maybe just inited
				return
			}

			const currentBuyNowProductPrice = currentBuyNowProductValues.product_price
			const buyNowProductQtyProperty = "current_buy_now_product_quantity"
			// get current quantity
			const currentBuyNowProductQuantity = parseInt(
				this.getStoreValue(buyNowProductQtyProperty)
			)

			let updatedBuyNowProductQuantity =
				currentBuyNowProductQuantity + parseInt(amount)
			if (!updatedBuyNowProductQuantity) {
				// make sure we always have at least quantity of 1
				// @todo: could also change for '0' to mean remove item?
				updatedBuyNowProductQuantity = 1
			}

			// update quantity
			this.setStoreValue(buyNowProductQtyProperty, updatedBuyNowProductQuantity)

			const currentBuyNowProductTotalPrice =
				updatedBuyNowProductQuantity * currentBuyNowProductPrice

			// update total price
			this.setCurrentBuyNowProductTotalPrice(currentBuyNowProductTotalPrice)
		},

		handUpdateCart() {
			debugLogger(`WE WILL TRIGGER HTMX TO TELL SERVER TO UPDATE CART!`)
			this.processBuyNow()
		},

		processBuyNow() {
			const currentBuyNowProductValues = this.getStoreValue(
				"current_buy_now_product_values"
			)
			const currentBuyNowProductID = parseInt(
				currentBuyNowProductValues.product_id
			)
			debugLogger(`BUY NOW PRODUCT ID: ${currentBuyNowProductID}`)
			if (currentBuyNowProductID) {
				// WE HAVE A CURRENT BUY NOW PRODUCT ID: process the modal!
				// this.$dispatch("HtmxAlpineTailwindDemosGetBuyNowProduct", {
				// 	current_buy_now_product_id: currentBuyNowProductID,
				// })
				debugLogger(
					`WE HAVE A BUY NOW PRODUCT ID: trigger htmx!: ${currentBuyNowProductID}`
				)
				const triggerElementID =
					"#htmx_alpine_tailwind_demos_get_buy_now_product_wrapper"
				const triggerEvent = "HtmxAlpineTailwindDemosGetBuyNowProduct"
				// const eventDetails = {
				// 	current_buy_now_product_id: currentBuyNowProductID,
				// }
				debugLogger(`triggerElementID for htmx!: ${triggerElementID}`)
				debugLogger(`triggerEvent for htmx!: ${triggerEvent}`)
				// debugLogger(`eventDetails for htmx!: ${eventDetails}`)
				// @NOTE: WE DELAY triggering htmx TO AVOID RACE CONDITION
				// @NOTE: delay:300ms won't work on htmx target since it won't detect the change in the hidden input on time
				setTimeout(() => {
					// htmx.trigger(triggerElementID, triggerEvent, eventDetails)
					htmx.trigger(triggerElementID, triggerEvent)
				}, 150)
			}
			// @TODO DO WE NEED TO HANDLE 'else'?
		},

		handleSomeAction() {
			const message = "htmx, Alpine.JS and Tailwind CSS are awesome!"
			// -----
			this.handleAnotherAction(message)
		},
		handleAnotherAction(message) {
			debugLogger(message)
		},

		setCurrentBuyNowProductValues(buy_now_product_values) {
			this.setStoreValue(
				"current_buy_now_product_values",
				buy_now_product_values
			)
			// also set 'current_buy_now_product_total_price'
			this.setCurrentBuyNowProductTotalPrice(
				buy_now_product_values.product_price
			)
		},

		setCurrentBuyNowProductTotalPrice(total_price) {
			this.setStoreValue("current_buy_now_product_total_price", total_price)
		},

		getCurrentTotalPrice() {
			// @note: we compute this to handle manual product quantity inputs
			let currentBuyNowProductTotalPrice = 0
			const currentBuyNowProductValues = this.getStoreValue(
				"current_buy_now_product_values"
			)

			const currentBuyNowProductID = parseInt(
				currentBuyNowProductValues.product_id
			)

			if (currentBuyNowProductID) {
				const currentBuyNowProductPrice = parseFloat(
					currentBuyNowProductValues.product_price
				)
				const currentBuyNowProductQuantity = parseInt(
					this.getStoreValue("current_buy_now_product_quantity")
				)
				currentBuyNowProductTotalPrice =
					currentBuyNowProductQuantity * currentBuyNowProductPrice
			}

			currentBuyNowProductTotalPrice = currentBuyNowProductTotalPrice.toFixed(2)
			//  ----
			return currentBuyNowProductTotalPrice
		},

		sendCustomEventToHTMX() {
			// @TODO
		},
	}))
})
