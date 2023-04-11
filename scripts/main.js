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
		// for the 'htmx renders modal' demo
		// current_buy_now_product_id: 0,
		current_buy_now_product_selected_variant_id: 0,
		current_buy_now_product_values: {},
		current_buy_now_product_variant_values: {},
		current_buy_now_product_variants_values: [],
		current_buy_now_product_quantity: 1,
		is_need_to_select_a_variant: false,
		// ----
		// @note: could be the product or its selected variant's price!
		current_buy_now_product_unit_price: 0,
		// ----
		current_buy_now_product_total_price: 0,
		ids_of_products_with_variants: [],
		all_products_variants: {},
		is_product_with_variants: false,
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

		initFetchedProductValues(product_values) {
			console.log("initFetchedProductValues - product_values", product_values)
			this.handleFetchBuyNowProduct(product_values)
		},

		handleBuyNow(product_values) {
			// ---------

			// @NOTE/@TODO HERE YOU SHOULD HANDLE ERRORS, ensure expected properties are in the sent object, etc!
			this.handleModalState()

			/* @TODO - PENDING:
			- htmx spinner when ajax request sent (?)
			*/

			// DOES CURRENT BUY NOW PRODUCT HAVE VARIANTS?
			const idsOfProductsWithVariants = this.getStoreValue(
				"ids_of_products_with_variants"
			)

			const currentBuyNowProductID = product_values.product_id
			if (
				Object.values(idsOfProductsWithVariants).includes(
					currentBuyNowProductID
				)
			) {
				// get and set this products variants
				// debugLogger(`current_buy_now_product_id - ${current_buy_now_product_id}`)
				const currentBuyNowProductVariants =
					this.getCurrentBuyNowProductVariants(currentBuyNowProductID)
				this.setCurrentBuyNowProductVariants(currentBuyNowProductVariants)
				// product has variants! - set flag to display them
				this.setStoreValue("is_product_with_variants", true)
				// disable product quantity and add to basket actions/elements until a variant is selected
				this.setStoreValue("is_need_to_select_a_variant", true)
			}
			// set current buy now product values to the store
			this.setCurrentBuyNowProductValues(product_values)
			// set current buy now product unit price to the store
			// @note: if product has variants, this will change once a variant is selected
			this.setStoreValue(
				"current_buy_now_product_unit_price",
				product_values.product_price
			)
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

			// const currentBuyNowProductPrice = currentBuyNowProductValues.product_price
			// @NOTE: IF PRODUCT HAS VARIANTS, this will be price of the selected variant
			// if the variant does not have a price, we get it from the parent product price
			const currentBuyNowProductUnitPrice = this.getStoreValue(
				"current_buy_now_product_unit_price"
			)
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
				updatedBuyNowProductQuantity * currentBuyNowProductUnitPrice

			// update total price
			this.setCurrentBuyNowProductTotalPrice(currentBuyNowProductTotalPrice)
		},

		handUpdateCart() {
			debugLogger(`WE WILL TRIGGER HTMX TO TELL SERVER TO UPDATE CART!`)
			this.processBuyNow()
		},

		handleFetchBuyNowProduct(product_values) {
			// @NOTE: for use by 'htmx renders modal' demo
			// @NOTE: QUITE SIMILAR TO handleBuyNow() with a few differences since we are loading most values from server
			// @TODO/WIP

			// @TODO THIS WILL NOW BE CALLED BY initFetchedProductValues() which will be called by the server response to htmx request! SO, MODAL WILL BE OPENED BY handleModalState()
			// --------
			console.log(
				"handleFetchBuyNowProduct - product values coming from product_values",
				product_values
			)
			console.log(
				"handleFetchBuyNowProduct - Object.keys(product_values)",
				Object.keys(product_values)
			)
			// @NOTE/@TODO HERE YOU SHOULD HANDLE ERRORS, ensure expected properties are in the sent object, etc!

			// DOES CURRENT BUY NOW PRODUCT HAVE VARIANTS?

			if (Object.keys(product_values).includes("variants")) {
				// @TODO CONFIRM WE CAN REUSE THIS FOR THE HTMX RENDERS MODALS DEMO!
				const currentBuyNowProductVariants = product_values["variants"]
				console.log(
					"handleFetchBuyNowProduct - product HAS VARIANTS - currentBuyNowProductVariants",
					currentBuyNowProductVariants
				)
				this.setCurrentBuyNowProductVariants(currentBuyNowProductVariants)
				// product has variants! - set flag to display them
				this.setStoreValue("is_product_with_variants", true)
				// disable product quantity and add to basket actions/elements until a variant is selected
				this.setStoreValue("is_need_to_select_a_variant", true)
			}
			// set current buy now product values to the store
			this.setCurrentBuyNowProductValues(product_values)
			// set current buy now product unit price to the store
			// @note: if product has variants, this will change once a variant is selected
			this.setStoreValue(
				"current_buy_now_product_unit_price",
				product_values.product_price
			)
		},

		handleModalState() {
			console.log(
				"handleModalState - WE NEED TO OPEN MODAL & SHOW SPINNER if fetching for htmx demo!"
			)
			const isModalOpenProperty = "is_modal_open"
			const currentIsModalOpenValue = this.getStoreValue(isModalOpenProperty)
			const incomingIsModalOpenValue = !currentIsModalOpenValue

			// --------
			// setTimeout(() => {
			// open or close modal for buy now
			this.setStoreValue(isModalOpenProperty, incomingIsModalOpenValue)
			// }, 300)

			// =========
			// if modal is closing, reset 'current by now product' values to defaults
			// also empty htmx populated notice for 'item added to basket'
			if (!incomingIsModalOpenValue) {
				console.log("handleModalState - MODAL IS CLOSING: RESET VALUES!")
				this.resetBuyNowValuesToDefaults()
			}
		},

		processBuyNow() {
			const currentBuyNowProductValues = this.getStoreValue(
				"current_buy_now_product_values"
			)
			const currentBuyNowProductID = parseInt(
				currentBuyNowProductValues.product_id
			)
			// debugLogger(`BUY NOW PRODUCT ID: ${currentBuyNowProductID}`)
			if (currentBuyNowProductID) {
				// WE HAVE A CURRENT BUY NOW PRODUCT ID: process the modal!
				// ----------
				// @note: $dispatch doesn't work with htmx
				// this.$dispatch("HtmxAlpineTailwindDemosGetBuyNowProduct", {
				// 	current_buy_now_product_id: currentBuyNowProductID,
				// })
				// debugLogger(
				// 	`WE HAVE A BUY NOW PRODUCT ID: trigger htmx!: ${currentBuyNowProductID}`
				// )
				const triggerElementID =
					"#htmx_alpine_tailwind_demos_get_buy_now_product_wrapper"
				const triggerEvent = "HtmxAlpineTailwindDemosGetBuyNowProduct"
				// const eventDetails = {
				// 	current_buy_now_product_id: currentBuyNowProductID,
				// }
				// debugLogger(`triggerElementID for htmx!: ${triggerElementID}`)
				// debugLogger(`triggerEvent for htmx!: ${triggerEvent}`)
				// debugLogger(`eventDetails for htmx!: ${eventDetails}`)
				// @NOTE: WE DELAY triggering htmx TO AVOID RACE CONDITION
				// @NOTE: delay:300ms won't work on htmx target since it won't detect the change in the hidden input on time
				setTimeout(() => {
					// @note: $dispatch doesn't work with htmx
					// this.$dispatch(triggerElementID, triggerEvent)
					// htmx.trigger(triggerElementID, triggerEvent, eventDetails)
					htmx.trigger(triggerElementID, triggerEvent)
					// @note: $dispatch doesn't work with htmx
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

		setProductsVariantsData(
			ids_of_products_with_variants,
			all_products_variants
		) {
			// SET TO STORE!
			// console.log(
			// 	"setProductsVariantsData - ids_of_products_with_variants",
			// 	ids_of_products_with_variants
			// )
			// console.log(
			// 	"setProductsVariantsData - all_products_variants",
			// 	all_products_variants
			// )
			this.setStoreValue(
				"ids_of_products_with_variants",
				ids_of_products_with_variants
			)
			this.setStoreValue("all_products_variants", all_products_variants)
		},
		setCurrentBuyNowProductVariants(currentBuyNowProductVariants) {
			console.log(
				"setCurrentBuyNowProductVariants - currentBuyNowProductVariants",
				currentBuyNowProductVariants
			)
			this.setStoreValue(
				"current_buy_now_product_variants_values",
				currentBuyNowProductVariants
			)
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

		setCurrentBuyNowProductSelectedVariant(buy_now_product_variant_values) {
			this.setStoreValue(
				"current_buy_now_product_selected_variant_id",
				parseInt(buy_now_product_variant_values.id)
			)
			this.setStoreValue(
				"current_buy_now_product_variant_values",
				buy_now_product_variant_values
			)
			// debugLogger(
			// 	`current_buy_now_variant_id: ${buy_now_product_variant_values.id}`
			// )
			// console.log(
			// 	"setCurrentBuyNowProductSelectedVariant - buy_now_product_variant_values",
			// 	buy_now_product_variant_values
			// )
			// -----
			// if we don't have a variant price, we fall back to the main product price
			let variantUnitPrice = buy_now_product_variant_values.price
			if (!variantUnitPrice) {
				const currentBuyNowProduct = this.getStoreValue(
					"current_buy_now_product_values"
				)
				// @note: property for main product is 'product_price'!
				variantUnitPrice = currentBuyNowProduct.product_price
			}
			// debugLogger(`variantUnitPrice: ${variantUnitPrice}`)
			this.setStoreValue("current_buy_now_product_unit_price", variantUnitPrice)
			this.setStoreValue("is_need_to_select_a_variant", false)
		},

		checkIsCurrentVariantID(variant_id) {
			const currentBuyNowProductVariantID = this.getStoreValue(
				"current_buy_now_product_selected_variant_id"
			)
			const isCurrentVariantID =
				parseInt(variant_id) === parseInt(currentBuyNowProductVariantID)
			return isCurrentVariantID
		},

		getProductOrSelectedVariantPrice() {
			// {$store}.current_buy_now_product_values.product_price
			const currentBuyNowProduct = this.getStoreValue(
				"current_buy_now_product_values"
			)
			const currentBuyNowProductVariant = this.getStoreValue(
				"current_buy_now_product_variant_values"
			)
			// console.log(
			// 	"getProductOrSelectedVariantPrice - currentBuyNowProduct",
			// 	currentBuyNowProduct
			// )
			// console.log(
			// 	"getProductOrSelectedVariantPrice - currentBuyNowProductVariant",
			// 	currentBuyNowProductVariant
			// )
			// console.log(
			// 	"getProductOrSelectedVariantPrice - Object.keys(currentBuyNowProductVariant)",
			// 	Object.keys(currentBuyNowProductVariant)
			// )
			// -------
			let productUnitPrice
			if (
				Object.keys(currentBuyNowProductVariant).length !== 0 &&
				currentBuyNowProductVariant.constructor === Object
			) {
				// productUnitPrice = currentBuyNowProductVariant.price
				productUnitPrice = this.getStoreValue(
					"current_buy_now_product_unit_price"
				)
				// console.log(
				// 	"getProductOrSelectedVariantPrice - productUnitPrice - VARIANT PRESENT",
				// 	productUnitPrice
				// )
			}
			if (!productUnitPrice) {
				// get price from main product (@note: 'product_price' is the prop!)
				productUnitPrice = currentBuyNowProduct.product_price
				// console.log(
				// 	"getProductOrSelectedVariantPrice - productUnitPrice - VARIANT NOT PRESENT OR NO UNIT PRICE",
				// 	productUnitPrice
				// )
			}
			return productUnitPrice
		},

		getCurrentBuyNowProductVariants(current_buy_now_product_id) {
			// allProductsVariants is an object of objects
			const allProductsVariants = this.getStoreValue("all_products_variants")
			// console.log(
			// 	"getCurrentBuyNowProductVariants - allProductsVariants",
			// 	allProductsVariants
			// )
			// ----
			// console.log(
			// 	"getCurrentBuyNowProductVariants - Object.keys(allProductsVariants)",
			// 	Object.keys(allProductsVariants)
			// )
			// console.log(
			// 	"getCurrentBuyNowProductVariants - Object.values(allProductsVariants)",
			// 	Object.values(allProductsVariants)
			// )
			// console.log(
			// 	"getCurrentBuyNowProductVariants - Object.entries(allProductsVariants)",
			// 	Object.entries(allProductsVariants)
			// )

			// ====
			// get the the values in the object of objects -> this is an Array
			// & filter it to get objects whose 'parent_id' match the ID of the current by now product
			const currentBuyNowProductVariants = Object.values(
				allProductsVariants
			).filter((item) => item.parent_id === current_buy_now_product_id)
			// for (variant of Object.values(allProductsVariants)) {
			// 	// body of for...of
			// 	console.log("getCurrentBuyNowProductVariants - variant", variant)
			// }

			// console.log(
			// 	"getCurrentBuyNowProductVariants - currentBuyNowProductVariants",
			// 	currentBuyNowProductVariants
			// )
			// ---------
			return currentBuyNowProductVariants
		},

		resetBuyNowValuesToDefaults() {
			console.log("resetBuyNowValuesToDefaults - RESETTING VALUES")
			// @note: just foolproofing as not really necessary as the values will be overwritten when modal is opened again
			// current_buy_now_product_values: {} // handled via blank values sent to handleBuyNow()
			// @TODO?
			// current_buy_now_product_total_price: 0,
			this.setStoreValue("current_buy_now_product_quantity", 1)
			this.setStoreValue("current_buy_now_product_selected_variant_id", 0)
			this.setStoreValue("is_product_with_variants", false)
			this.setStoreValue("current_buy_now_product_variants_values", [])
			// -
			// empty htmx populated notice for item added to basket
			this.$refs.htmx_alpine_tailwind_demos_get_buy_now_product_notice.replaceChildren()
			// FOR 'htmx renders modal' demo only
			// empty htmx populated buy now products details for item to add to basket
			if (this.$refs.htmx_alpine_tailwind_demos_fetch_buy_now_product_wrapper) {
				this.$refs.htmx_alpine_tailwind_demos_fetch_buy_now_product_wrapper.replaceChildren()
			}
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
				// const currentBuyNowProductPrice = parseFloat(
				// 	currentBuyNowProductValues.product_price
				// )
				const currentBuyNowProductUnitPrice = this.getStoreValue(
					"current_buy_now_product_unit_price"
				)
				const currentBuyNowProductQuantity = parseInt(
					this.getStoreValue("current_buy_now_product_quantity")
				)
				currentBuyNowProductTotalPrice =
					currentBuyNowProductQuantity * currentBuyNowProductUnitPrice
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
