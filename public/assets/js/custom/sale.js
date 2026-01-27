$(document).ready(function () {
    var $sidebarPlan = $(".lg-sub-plan");
    var $subPlan = $(".sub-plan");
    var isActive = $(window).width() >= 1150;

    $(".side-bar, .section-container").toggleClass("active", isActive);

    if (isActive) {
        $sidebarPlan.hide();
        $subPlan.show();

        $(".side-bar-addon, .side-bar-addon-2, .side-bar-addon-3").hide();
    } else {
        $sidebarPlan.show();
        $subPlan.hide();

        $(".side-bar-addon, .side-bar-addon-2, .side-bar-addon-3").show();
    }
});

// currency format
function currencyFormat(amount, type = "icon", decimals = 2) {
    let symbol = $("#currency_symbol").val();
    let position = $("#currency_position").val();
    let code = $("#currency_code").val();

    let formatted_amount = formattedAmount(amount, decimals);

    // Apply currency format based on the position and type
    if (type === "icon" || type === "symbol") {
        if (position === "right") {
            return formatted_amount + symbol;
        } else {
            return symbol + formatted_amount;
        }
    } else {
        if (position === "right") {
            return formatted_amount + " " + code;
        } else {
            return code + " " + formatted_amount;
        }
    }
}

// Format the amount
function formattedAmount(amount, decimals) {
    return Number.isInteger(+amount)
        ? parseInt(amount)
        : (+amount).toFixed(decimals);
}

// get number only
function getNumericValue(value) {
    return parseFloat(value.replace(/[^0-9.-]+/g, "")) || 0;
}

function assetPath(path) {
    let baseUrl = $("#asset_base_url").val() || "";
    return baseUrl.replace(/\/$/, "") + "/" + path.replace(/^\/+/, "");
}

// Round option wise round amount
function RoundingTotal(amount) {
    let option = $("#rounding_amount_option").val();
    if (option === "round_up") {
        return Math.ceil(amount);
    } else if (option === "nearest_whole_number") {
        return Math.round(amount);
    } else if (option === "nearest_0.05") {
        return Math.round(amount * 20) / 20;
    } else if (option === "nearest_0.1") {
        return Math.round(amount * 10) / 10;
    } else if (option === "nearest_0.5") {
        return Math.round(amount * 2) / 2;
    } else {
        return amount;
    }
}

// Update the cart list and call the callback once complete
function fetchUpdatedCart(callback) {
    let url = $("#get-cart").val();
    $.ajax({
        url: url,
        type: "GET",
        success: function (response) {
            $("#cart-list").html(response);
            if (typeof callback === "function") callback(); // Call the callback after updating the cart
        },
    });
}

// Update price
$(document).on("change", ".cart-price", function () {
    let $row = $(this).closest("tr");
    let rowId = $row.data("row_id");
    let updateRoute = $row.data("update_route");
    let newPrice = parseFloat($(this).val());

    if (newPrice < 0 || isNaN(newPrice)) {
        toastr.error("Price can not be negative.");
        return;
    }
    let currentQty = parseFloat($row.find(".cart-qty").val());

    updateCart(rowId, currentQty, updateRoute, newPrice);
});

// Increase quantity
$(document).on("click", ".plus-btn", function (e) {
    e.preventDefault();
    let $row = $(this).closest("tr");
    let rowId = $row.data("row_id");
    let updateRoute = $row.data("update_route");
    let $qtyInput = $row.find(".cart-qty");
    let currentQty = parseFloat($qtyInput.val());
    let newQty = currentQty + 1;
    $qtyInput.val(newQty);

    // Get the current price
    let currentPrice = parseFloat($row.find(".cart-price").val());

    if (isNaN(currentPrice) || currentPrice < 0) {
        toastr.error("Price can not be negative.");
        return;
    }
    updateCart(rowId, newQty, updateRoute, currentPrice);
});

// Decrease quantity
$(document).on("click", ".minus-btn", function (e) {
    e.preventDefault();
    let $row = $(this).closest("tr");
    let rowId = $row.data("row_id");
    let updateRoute = $row.data("update_route");
    let $qtyInput = $row.find(".cart-qty");
    let currentQty = parseFloat($qtyInput.val());

    // Ensure quantity does not go below 1
    if (currentQty > 1) {
        let newQty = currentQty - 1;
        $qtyInput.val(newQty);

        // Get the current price
        let currentPrice = parseFloat($row.find(".cart-price").val());
        if (isNaN(currentPrice) || currentPrice < 0) {
            toastr.error("Price can not be negative.");
            return;
        }

        // Call updateCart with both qty and price
        updateCart(rowId, newQty, updateRoute, currentPrice);
    }
});

// Cart quantity input field change event
$(document).on("change", ".cart-qty", function () {
    let $row = $(this).closest("tr");
    let rowId = $row.data("row_id");
    let updateRoute = $row.data("update_route");
    let newQty = parseFloat($(this).val());

    // Retrieve the cart price
    let currentPrice = parseFloat($row.find(".cart-price").val());
    if (isNaN(currentPrice) || currentPrice < 0) {
        toastr.error("Price can not be negative.");
        return;
    }

    // Ensure quantity does not go below 0
    if (newQty >= 0) {
        updateCart(rowId, newQty, updateRoute, currentPrice);
    }
});

// Remove item from the cart
$(document).on("click", ".remove-btn", function (e) {
    e.preventDefault();
    var $row = $(this).closest("tr");
    var destroyRoute = $row.data("destroy_route");

    $.ajax({
        url: destroyRoute,
        type: "DELETE",
        success: function (response) {
            if (response.success) {
                // Item was successfully removed, fade out and remove the row from DOM
                $row.fadeOut(400, function () {
                    $(this).remove();
                });
                // Recalculate and update cart totals
                fetchUpdatedCart(calTotalAmount);
            } else {
                toastr.error(response.message || "Failed to remove item");
            }
        },
        error: function () {
            toastr.error("Error removing item from cart");
        },
    });
});

// Function to update cart item with the new quantity
function updateCart(rowId, qty, updateRoute, price) {
    $.ajax({
        url: updateRoute,
        type: "PUT",
        data: {
            rowId: rowId,
            qty: qty,
            price: price,
        },
        success: function (response) {
            if (response.success) {
                fetchUpdatedCart(calTotalAmount); // Refresh the cart and recalculate totals
            } else {
                toastr.error(response.message || "Failed to update cart");
            }
        },
    });
}

// Clear the cart and then refresh the UI with updated values
function clearCart(cartType) {
    let route = $("#clear-cart").val();
    $.ajax({
        type: "POST",
        url: route,
        data: {
            type: cartType,
        },
        dataType: "json",
        success: function (response) {
            fetchUpdatedCart(calTotalAmount); // Call calTotalAmount after cart fetch completes
        },
        error: function () {
            console.error("There was an issue clearing the cart.");
        },
    });
}

/** Handle customer selection change **/
$(".customer-select").on("change", function () {
    let customer_type = $(this).find(":selected").data("type");
    let $customer_id = $(this).val();

    // Delay clearing cart slightly to avoid race condition
    setTimeout(() => {
        clearCart("sale");
        $(".payment-section input").val("");
    }, 100);

    // Check if the customer value is "guest"
    if ($customer_id === "guest") {
        $(".guest_phone").removeClass("d-none");
        $("#customer_phone").prop("required", true);

        // Reset product prices to their default (Retailer prices)
        $(".single-product").each(function () {
            let defaultPrice = $(this).data("default_price");
            $(this).find(".product_price").text(currencyFormat(defaultPrice));
        });
    } else {
        $(".guest_phone").addClass("d-none");
        $(".guest_phone input").val("");
        $("#customer_phone").prop("required", false);
        // Update product prices based on the selected customer type
        if (customer_type) {
            let url = $("#get_product").val();
            $.ajax({
                url: url,
                type: "GET",
                data: { type: customer_type },
                success: function (data) {
                    $(".single-product").each(function () {
                        let productId = $(this).data("product_id");
                        if (data[productId]) {
                            $(this)
                                .find(".product_price")
                                .text(data[productId]);
                        }
                    });
                },
            });
        }
    }
});

// Trigger calculation whenever Discount, or Receive Amount fields change
$("#discount_amount, #receive_amount, #shipping_charge").on(
    "input",
    function () {
        calTotalAmount();
    }
);

// gst calculation
$(".vat_select").on("change", function () {
    let vatRate = parseFloat($(this).find(":selected").data("rate")) || 0;
    let subtotal = getNumericValue($("#sub_total").text()) || 0;

    let vatAmount = (subtotal * vatRate) / 100;

    $("#vat_amount").val(vatAmount.toFixed(2));
    calTotalAmount();
});

// discount calculation
$(".discount_type").on("change", function () {
    calTotalAmount();
});

// Function to calculate the total amount
function calTotalAmount() {
    let subtotal = 0;

    // Calculate subtotal from cart list using qty * price
    $("#cart-list tr").each(function () {
        let qty = getNumericValue($(this).find(".cart-qty").val()) || 0;
        let price = getNumericValue($(this).find(".cart-price").val()) || 0;
        let row_subtotal = qty * price;
        subtotal += row_subtotal;
    });

    $("#sub_total").text(currencyFormat(subtotal));

    // GST
    let vat_rate =
        parseFloat($(".vat_select option:selected").data("rate")) || 0;
    let vat_amount = (subtotal * vat_rate) / 100;
    $("#vat_amount").val(vat_amount.toFixed(2));

    // Subtotal with GST
    let subtotal_with_vat = subtotal + vat_amount;

    // Discount
    let discount_amount = getNumericValue($("#discount_amount").val()) || 0;
    let discount_type = $(".discount_type").val();

    if (discount_type === "percent") {
        discount_amount = (subtotal_with_vat * discount_amount) / 100;

        if (discount_amount > subtotal_with_vat) {
            toastr.error("Discount cannot be more than 100% of the amount!");
            discount_amount = subtotal_with_vat;
            $("#discount_amount").val(100);
        }
    } else {
        if (discount_amount > subtotal_with_vat) {
            toastr.error("Discount cannot be more than the amount!");
            discount_amount = subtotal_with_vat;
            $("#discount_amount").val(discount_amount);
        }
    }

    // Shipping Charge
    let shipping_charge = getNumericValue($("#shipping_charge").val()) || 0;

    // Total Amount
    let total_amount = subtotal_with_vat + shipping_charge - discount_amount;
    $("#total_amount").text(currencyFormat(total_amount));

    // Rounding total
    let rounding_total = RoundingTotal(total_amount);

    // Rounding off
    let rounding_amount = Math.abs(rounding_total - total_amount);
    $("#rounding_amount").text(currencyFormat(rounding_amount));

    // Payable Amount
    let payable_amount = rounding_total;
    $("#payable_amount").text(currencyFormat(payable_amount));

    // Receive Amount
    let receive_amount = getNumericValue($("#receive_amount").val()) || 0;
    if (receive_amount < 0) {
        toastr.error("Receive amount cannot be less than 0!");
        receive_amount = 0;
        $("#receive_amount").val(receive_amount);
    }

    // Change Amount
    let change_amount =
        receive_amount > payable_amount ? receive_amount - payable_amount : 0;
    $("#change_amount").val(formattedAmount(change_amount, 2));

    // Due Amount
    let due_amount =
        payable_amount > receive_amount ? payable_amount - receive_amount : 0;
    $("#due_amount").val(formattedAmount(due_amount, 2));
}

calTotalAmount();

// Cancel btn action
$(".cancel-sale-btn").on("click", function (e) {
    e.preventDefault();
    clearCart("sale");
});

// Category Filter
$(".category-search").on("input", function (e) {
    e.preventDefault();
    // Get search query
    const search = $(this).val();
    const route = $(this).closest("form").data("route");

    $.ajax({
        type: "POST",
        url: route,
        data: {
            search: search,
        },
        success: function (response) {
            $("#category-data").html(response.categories);
        },
    });
});

// brand Filter
$(".brand-search").on("input", function (e) {
    e.preventDefault();

    // Get search query
    const search = $(this).val();

    const route = $(this).closest("form").data("route");

    $.ajax({
        type: "POST",
        url: route,
        data: {
            search: search,
        },
        success: function (response) {
            $("#brand-data").html(response.brands);
        },
    });
});

// select brand or product action
$(document).on("click", ".category-list, .brand-list", function () {
    const isCategory = $(this).hasClass("category-list");
    const filterType = isCategory ? "category_id" : "brand_id";
    const filterId = $(this).data("id");
    const route = $(this).data("route"); // product filter route

    const searchTerm = $("#sale_product_search").val();

    $.ajax({
        type: "POST",
        url: route,
        data: {
            search: searchTerm,
            [filterType]: filterId, // Dynamically set category_id or brand_id
        },
        success: function (response) {
            $("#products-list").html(response.data);
            $("#category-list").html(response.categories);
            $("#brand-list").html(response.brands);
        },
    });
});

/** Add to cart functionality start **/

// Debounce function to limit the frequency of API calls
function debounce(func, delay) {
    let timer;
    return function (...args) {
        const context = this;
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(context, args), delay);
    };
}

// Scanner detection variables
let isScannerInput = false;
let scannerInputTimeout;
const SCANNER_LOCK_TIME = 300; // Time to wait before allowing another scan

// Handle scanner input when Enter key is pressed
$(".product-filter").on("keydown", ".search-input", function (e) {
    if (e.key === "Enter") {
        if (isScannerInput) {
            e.preventDefault();
            return; // Skip duplicate scanner calls
        }

        e.preventDefault(); // Prevent form submission

        handleScannerInput(this);
    }
});

$(".product-filter").on("submit", function (e) {
    e.preventDefault();
});

// Trigger input handler on user typing (debounced)
$(".product-filter").on(
    "input",
    ".search-input",
    debounce(function () {
        if (isScannerInput) {
            return; // Skip input events triggered by scanner
        }

        handleUserInput();
    }, 400)
);

// Function to handle scanner input
function handleScannerInput(inputElement) {
    isScannerInput = true; // Lock scanner input handling
    clearTimeout(scannerInputTimeout); // Reset scanner lock timer

    const form = $(inputElement).closest("form")[0];
    const customer_id = $(".customer-select").val();

    if (!customer_id) {
        toastr.warning("Please select a customer first!");
        resetScannerLock();
        return;
    }

    $.ajax({
        type: "POST",
        url: $(form).attr("action"),
        data: new FormData(form),
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        success: function (res) {
            if (res.total_products && res.product_id) {
                autoAddItemToCart(res.product_id);
            }
            // $("#products-list").html(res.data); // Update the table with new data
            // change price according customer-type
            customerWisePrice();
        },
        complete: function () {
            resetScannerLock();
        },
    });
}

// Function to handle user input
function handleUserInput() {
    const customer_id = $(".customer-select").val();

    if (!customer_id) {
        toastr.warning("Please select a customer first!");
        return;
    }

    fetchProducts();
}

// Reset scanner lock after processing
function resetScannerLock() {
    scannerInputTimeout = setTimeout(() => {
        isScannerInput = false;
    }, SCANNER_LOCK_TIME);
}

// Fetch products function
function fetchProducts() {
    const form = $(".product-filter-form")[0];

    $.ajax({
        type: "POST",
        url: $(form).attr("action"),
        data: new FormData(form),
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        success: function (res) {
            $("#products-list").html(res.data); // Update the table with new data

            if (
                res.total_products &&
                res.product_id &&
                res.total_products_count > 1
            ) {
                autoAddItemToCart(res.product_id);
            }

            // change price according customer-type
            customerWisePrice();
        },
    });
}

// Customer Wise Product Price Change
function customerWisePrice() {
    let customer_type =
        $(".customer-select option:selected").data("type") || "Retailer";
    if (customer_type && customer_type !== "Retailer") {
        let url = $("#get_product").val();

        $.ajax({
            url: url,
            type: "GET",
            data: { type: customer_type },
            success: function (data) {
                $(".single-product").each(function () {
                    let productId = $(this).data("product_id");
                    if (data[productId]) {
                        $(this).find(".product_price").text(data[productId]);
                    }
                });
            },
        });
    }
}

// ------------------------
// Utility Functions
// ------------------------
function getCustomerType() {
    return $(".customer-select option:selected").data("type") || "Retailer";
}

function getAvailableStocks(stocks) {
    return Array.isArray(stocks)
        ? stocks.filter((stock) => parseFloat(stock.productStock) >= 1)
        : [];
}

function getAdjustedPrice(batch, customerType) {
    if (customerType === "Dealer" && batch.productDealerPrice) {
        return batch.productDealerPrice;
    } else if (customerType === "Wholesaler" && batch.productWholeSalePrice) {
        return batch.productWholeSalePrice;
    }
    return batch.productSalePrice;
}

function prepareSingleBatchItem(item, batch, customerType) {
    item.data("product_stock_id", batch.id);
    item.data("product_expire_date", batch.expire_date);
    item.data("default_price", getAdjustedPrice(batch, customerType));
    return item;
}

function showBatchSelectionModal(element, availableStocks, customerType) {
    const showWarehouse = $("#warehouse_module_exist").val() == "1";

    let html = availableStocks
        .map((batch, index) => {
            const adjustedPrice = getAdjustedPrice(batch, customerType);

            return `
            <tr class="select-batch"
                data-product_id="${element.data("product_id")}"
                data-product_stock_id="${batch.id}"
                data-product_expire_date="${batch.expire_date ?? ""}"
                data-product_name="${element.data("product_name")}"
                data-product_code="${element.data("product_code")}"
                data-default_price="${adjustedPrice}"
                data-product_unit_id="${element.data("product_unit_id")}"
                data-product_unit_name="${element.data("product_unit_name")}"
                data-purchase_price="${batch.productPurchasePrice ?? 0}"
                data-product_image="${element.data("product_image")}"
                data-route="${element.data("route")}">
                    <td>${index + 1}</td>
                    <td class="text-start">${element.data("product_name")}</td>
                    <td>${batch.batch_no ?? "N/A"}</td>
                    ${showWarehouse ? `<td>${batch.warehouse?.name ?? ""}</td>` : ""}
                    <td>${batch.productStock ?? "N/A"}</td>
                    <td class="product_price">${currencyFormat(
                        adjustedPrice
                    )}</td>
            </tr>`;
        })
        .join("");

    $(".stock-table").html(html);
    $("#stock-list-modal").modal("show");
}

// ------------------------
// Core Add-to-Cart Logic
// ------------------------
function handleAddToCart(element) {
    const batchCount = parseInt(element.data("batch_count")) || 0;
    const stocks = Array.isArray(element.data("stocks"))
        ? element.data("stocks")
        : [];
    const customerType = getCustomerType();
    const availableStocks = getAvailableStocks(stocks);

    if (batchCount > 1 && availableStocks.length > 0) {
        showBatchSelectionModal(element, availableStocks, customerType);
        return;
    }

    // Only one batch or no modal needed
    const singleBatch = stocks[0] ?? {};
    const item = prepareSingleBatchItem(element, singleBatch, customerType);

    addItemToCart(item);
}

// ------------------------
// Auto Add From Scanner
// ------------------------
function autoAddItemToCart(id) {
    const element = $("#products-list").find(".single-product." + id);
    handleAddToCart(element);
}

// ------------------------
// Click Event Binding
// ------------------------
$(document).on("click", "#single-product", function () {
    const customer_id = $(".customer-select").val();

    if (!customer_id) {
        toastr.warning("Please select a customer first!");
        resetScannerLock();
        return;
    }

    handleAddToCart($(this));
});

// Handle click on a batch inside the modal
$(document).on("click", ".select-batch", function () {
    addItemToCart($(this));
    $("#stock-list-modal").modal("hide");
});

// search filter in modal
$(document).on("keyup", ".stock-search", function () {
    let value = $(this).val().toLowerCase();
    $(".stock-table tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

// Final cart addition logic
function addItemToCart(element) {
    let url = element.data("route");
    let product_id = element.data("product_id");
    let product_name = element.data("product_name");
    let extractedPrice = getNumericValue(element.find(".product_price").text());
    let product_price =
        !isNaN(extractedPrice) && extractedPrice > 0
            ? extractedPrice
            : parseFloat(element.data("default_price")) || 0;
    let product_code = element.data("product_code");
    let product_unit_id = element.data("product_unit_id");
    let product_unit_name = element.data("product_unit_name");
    let product_stock_id = element.data("product_stock_id");
    let product_expire_date = element.data("product_expire_date");
    let purchase_price = element.data("purchase_price");
    let product_image = element.data("product_image");

    $.ajax({
        url: url,
        type: "POST",
        data: {
            type: "sale",
            id: product_id,
            name: product_name,
            price: product_price,
            quantity: 1,
            product_code: product_code,
            product_unit_id: product_unit_id,
            product_unit_name: product_unit_name,
            stock_id: product_stock_id,
            expire_date: product_expire_date,
            purchase_price: purchase_price,
            product_image: product_image,
        },
        success: function (response) {
            if (response.success) {
                fetchUpdatedCart(calTotalAmount); // Update totals after cart fetch completes
                $("#sale_product_search").val("");
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr) {
            console.error("Error:", xhr.responseText);
        },
    });
}

/** Add to Cart Functionality End **/

/** INVENTORY SALE START **/
// customer selection triggers product price update based on type (guest/dealer/etc)
$(".inventory-customer-select").on("change", function () {
    let customer_type = $(this).find(":selected").data("type");
    let $customer_id = $(this).val();

    // slight delay to prevent race condition
    setTimeout(() => {
        clearCart("sale");
        $(".payment-section input").val("");
    }, 100);

    if ($customer_id === "guest") {
        $(".guest_phone").removeClass("d-none");
        $(".guest_phone #customer_phone").prop("required", true);

        // show default (retail) price from batch data
        $(".single-product").each(function () {
            $(this)
                .find(".product_price")
                .each(function () {
                    let defaultPrice = $(this)
                        .closest(".add-batch-item, .single-product")
                        .data("default_price");
                    $(this).text(currencyFormat(defaultPrice));
                });
        });
    } else if ($customer_id) {
        $(".guest_phone").addClass("d-none");
        $(".guest_phone input").val("");
        $(".guest_phone #customer_phone").prop("required", false);

        if (customer_type) {
            let url = $("#get_stock_prices").val();
            $.ajax({
                url: url,
                type: "GET",
                data: { type: customer_type },
                success: function (data) {
                    // update price per stock id from response
                    $(".single-product").each(function () {
                        $(this)
                            .find(".product_price")
                            .each(function () {
                                let stockId = $(this)
                                    .closest(".add-batch-item, .single-product")
                                    .data("stock_id");
                                if (data[stockId]) {
                                    $(this).text(data[stockId]);
                                }
                            });
                    });
                },
            });
        }
    }
});

// renders product dropdown list with batch-level stock/pricing info
function populateProducts(products) {
    const $dropdownList = $("#dropdownList");
    $dropdownList.empty();

    if (products.length === 0) {
        $dropdownList.append(
            '<div class="product-option-item">No products available</div>'
        );
        return;
    }

    const cartRoute = $("#cart-store-url").val();

    $.each(products, function (index, product) {
        const imageUrl = assetPath(
            product.productPicture ?? "assets/images/products/box.svg"
        );
        let html = "";

        // if multiple stocks
        if (product.stocks && product.stocks.length > 1) {
            html += `<div class="product-option-item single-product"
                data-product_id="${product.id}"
                data-route="${cartRoute}"
                data-product_name="${product.productName}"
                data-product_code="${product.productCode}"
                data-product_unit_id="${product.unit_id}"
                data-product_unit_name="${product.unit?.unitName ?? ""}"
                data-product_image="${product.productPicture}">
                <div class="product-left">
                    <img src="${imageUrl}" alt="">
                    <div class="product-text">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="product-title">${
                                product.productName
                            }</div>
                            <p>Code : ${product.productCode}</p>
                        </div>`;

            product.stocks.forEach((stock) => {
                html += `<div class="d-flex align-items-center justify-content-between w-100 multi-items add-batch-item"
                        data-product_stock_id="${stock.id}"
                        data-product_id="${product.id}"
                        data-product_expire_date="${stock.expire_date ?? ""}"
                        data-product_name="${product.productName}"
                        data-product_code="${product.productCode}"
                        data-default_price="${stock.productSalePrice}"
                        data-product_unit_id="${product.unit_id}"
                        data-product_unit_name="${product.unit?.unitName ?? ""}"
                        data-purchase_price="${stock.productPurchasePrice}"
                        data-product_image="${product.productPicture}"
                        data-route="${cartRoute}">
                        <div class="product-des">
                            Batch: ${stock.batch_no ?? "N/A"}${
                    product.color ? ", Color: " + product.color : ""
                }${
                    stock.warehouse?.name
                        ? ", Warehouse: " + stock.warehouse.name + ","
                        : ""
                }
                            <span class="product-in-stock"> In Stock: ${
                                stock.productStock ?? 0
                            } </span>
                        </div>
                        <div class="product-price product_price">${currencyFormat(
                            stock.productSalePrice
                        )}</div>
                    </div>`;
            });

            html += `</div></div></div>`;
        } else {
            const singleStock =
                Array.isArray(product.stocks) && product.stocks.length > 0
                    ? product.stocks[0]
                    : {};

            html += `<div class="product-option-item single-product ${
                product.id
            } add-batch-item"
                data-product_stock_id="${singleStock.id ?? ""}"
                data-product_id="${product.id}"
                data-default_price="${singleStock.productSalePrice ?? 0}"
                data-product_name="${product.productName}"
                data-product_code="${product.productCode}"
                data-product_unit_id="${product.unit_id}"
                data-product_unit_name="${product.unit?.unitName ?? ""}"
                data-purchase_price="${singleStock.productPurchasePrice ?? 0}"
                data-product_image="${product.productPicture}"
                data-product_expire_date="${singleStock.expire_date ?? ""}"
                data-route="${cartRoute}">
                <div class="product-left">
                    <img src="${imageUrl}" alt="">
                    <div class="product-text">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="product-title">${
                                product.productName
                            }</div>
                            <p>Code : ${product.productCode}</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between w-100">
                           <div class="product-des">
    Batch: ${singleStock.batch_no ?? "N/A"},
    ${product.color ? ", Color: " + product.color : ""}
    ${
        singleStock.warehouse?.name
            ? ", Warehouse: " + singleStock.warehouse.name
            : ""
    }
    <span class="product-in-stock">
        In Stock: ${singleStock.productStock ?? 0}
    </span>
</div>


                            <div class="product-price product_price">${currencyFormat(
                                singleStock.productSalePrice ?? 0
                            )}</div>
                        </div>
                    </div>
                </div>
            </div>`;
        }

        $dropdownList.append(html);
    });
}

// load all products initially
const allProductsUrl = $("#all-products").val();
if (allProductsUrl) {
    $.get(allProductsUrl, function (products) {
        populateProducts(products);
    });
}

// filter by category selection
$("#categorySelect").on("change", function () {
    const categoryId = $(this).val();
    const categoryUrlBase = $("#get-by-category").val();
    const allProductsUrl = $("#all-products").val();

    if (categoryId && categoryUrlBase) {
        const url = categoryUrlBase + "/" + categoryId;
        $.get(url, function (products) {
            populateProducts(products);
        });
    } else if (allProductsUrl) {
        $.get(allProductsUrl, function (products) {
            populateProducts(products);
        });
    }
});

// toggle product dropdown visibility
const $dropdown = $("#dropdownList");
const $searchContainer = $("#searchContainer");
const $productSearch = $("#productSearch");
const $selectedText = $("#selectedValue");
const $selectedValueInput = $("#selectedProductValue");
const $arrow = $("#arrow");

$("#productDropdown .product-selected").on("click", function (e) {
    e.stopPropagation();
    $dropdown.toggle();
    $searchContainer.toggleClass("hidden");
    $arrow.toggleClass("product-rotate");
});

// Add to cart on batch click
$("#dropdownList").on("click", ".add-batch-item", function (e) {
    e.stopPropagation();

    let customer_id = $(".inventory-customer-select").val();
    if (!customer_id) {
        toastr.warning("Please select a customer first!");
        return;
    }

    const value = $(this).data("value") ?? "";
    const text =
        $(this).find(".product-title").text()?.trim() ||
        $(this).data("product_name");

    $selectedText.text(text);
    $selectedValueInput.val(value);
    $dropdown.hide();
    $searchContainer.addClass("hidden");
    $arrow.removeClass("product-rotate");

    addItemToCart($(this));
});

// Close dropdown and search if clicked outside
$(document).on("click", function (e) {
    if (!$(e.target).closest("#productDropdown").length) {
        $dropdown.hide();
        $searchContainer.addClass("hidden");
        $arrow.removeClass("product-rotate");
    }
});

// product search
$productSearch.on("keyup", function () {
    const searchTerm = $(this).val().toLowerCase();

    $("#dropdownList .product-option-item").each(function () {
        const name = String($(this).data("product_name") || "").toLowerCase();
        const code = String($(this).data("product_code") || "").toLowerCase();

        $(this).toggle(name.includes(searchTerm) || code.includes(searchTerm));
    });
});

/** INVENTORY SALE END **/

// Function to add customer to dropdown and select it (global function)
window.addCustomerToDropdown = function(customer) {
    console.log('Adding customer to dropdown:', customer);
    
    // Add customer to all customer dropdowns
    $('.customer-select, .inventory-customer-select').each(function() {
        let $select = $(this);
        let selectElement = this;
        
        // Check if customer already exists
        if ($select.find('option[value="' + customer.id + '"]').length > 0) {
            console.log('Customer already exists in dropdown');
            // Just select it
            selectCustomerInDropdown($select, selectElement, customer.id);
            return;
        }
        
        // Create new option element
        let $option = $('<option>', {
            value: customer.id,
            text: customer.option_text || (customer.name + '(' + customer.type + (customer.due_text || '') + ') ' + (customer.phone || '')),
            'data-type': customer.type,
            'data-phone': customer.phone || ''
        });
        
        // Insert before guest option, or at the end if guest option doesn't exist
        let $guestOption = $select.find('option.guest-option');
        if ($guestOption.length > 0) {
            $guestOption.before($option);
        } else {
            $select.append($option);
        }
        
        // For Choices.js dropdowns, we need to refresh the Choices instance
        if ($select.hasClass('choices-select')) {
            // Destroy and recreate Choices to include the new option
            if (selectElement.choices) {
                selectElement.choices.destroy();
            }
            
            // Recreate Choices instance
            let choicesInstance = new Choices(selectElement, {
                searchEnabled: true,
                itemSelectText: "",
                shouldSort: false,
            });
            
            // Select the customer after a short delay
            setTimeout(function() {
                try {
                    choicesInstance.setChoiceByValue(customer.id.toString());
                    $select.trigger('change');
                    console.log('Customer selected in Choices dropdown');
                } catch (e) {
                    console.error('Error selecting in Choices:', e);
                    $select.val(customer.id).trigger('change');
                }
            }, 100);
        } else {
            // For regular select dropdowns, just set the value
            $select.val(customer.id).trigger('change');
            console.log('Customer selected in regular dropdown');
        }
    });
};

// Helper function to select customer in dropdown
function selectCustomerInDropdown($select, selectElement, customerId) {
    if ($select.hasClass('choices-select')) {
        if (selectElement.choices) {
            try {
                selectElement.choices.setChoiceByValue(customerId.toString());
                $select.trigger('change');
            } catch (e) {
                $select.val(customerId).trigger('change');
            }
        } else {
            $select.val(customerId).trigger('change');
        }
    } else {
        $select.val(customerId).trigger('change');
    }
}

// Phone number validation for customer phone field
$(document).on("input", "#customer_phone", function () {
    let phone = $(this).val().replace(/[^0-9]/g, "");
    $(this).val(phone);
    
    if (phone.length > 10) {
        $(this).val(phone.substring(0, 10));
    }
});

// Validate phone number before form submission
$(document).on("submit", ".ajaxform", function (e) {
    let $customerSelect = $(this).find(".customer-select, .inventory-customer-select");
    let customerId = $customerSelect.val();
    let $customerPhone = $(this).find("#customer_phone");
    
    // If guest is selected, validate phone number
    if (customerId === "guest") {
        let phone = $customerPhone.val() ? $customerPhone.val().replace(/[^0-9]/g, "") : "";
        
        if (!phone || phone.length !== 10) {
            e.preventDefault();
            toastr.error("Please enter a valid 10-digit phone number for guest customer.");
            $customerPhone.focus();
            return false;
        }
        
        // Update the value to ensure it's clean
        $customerPhone.val(phone);
    }
});
