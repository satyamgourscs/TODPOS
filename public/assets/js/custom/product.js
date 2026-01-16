"use strict";
// product img upload code start ---------------->

$(document).ready(function () {
    $(".upload-box").each(function () {
        if ($(this).find(".preview-wrapper").length > 0) {
            $(this).find("svg").first().hide();
            $(this).find(".upload-text").hide();
        }
    });
});

$(document).on("change", ".handle-image-Upload", function (e) {
    const input = this;
    const file = input.files[0];
    if (!file) return;

    const $box = $(input).closest(".upload-box");

    $box.find("svg").first().hide();
    $box.find(".upload-text").hide();

    $box.find(".preview-wrapper").remove();

    const previewURL = URL.createObjectURL(file);
    const previewHTML = `
        <div class="preview-wrapper">
            <img class="preview-img" src="${previewURL}">
            <span class="close-icon">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 4L4 12" stroke="#C52127" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4 4L12 12" stroke="#C52127" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
    `;

    $box.append(previewHTML).addClass("uploaded");
});

$(document).on("click", ".close-icon", function () {
    const $box = $(this).closest(".upload-box");
    $box.find(".preview-wrapper").remove();
    $box.find("input[type='file']").val("");
    $box.find("svg").first().show();
    $box.find(".upload-text").show();

    $box.removeClass("uploaded");
});

// product img upload code end---------------->

// variant wise input field change
const $singleRadio = $("#singleOption");
const $variantRadio = $("#variantOption");
const $singleContainer = $(".single-container");
const $variantContainer = $(".variant-container");
const $warehouseContainer = $(".warehouse_id");


function toggleContainers() {
    if ($singleRadio.is(":checked")) {
        $singleContainer.show();
        $variantContainer.hide();

        // Disable and clear multiple variant fields
        $variantContainer.find("input, select").prop("disabled", true);
        $singleContainer.find("input, select").prop("disabled", false);

        $warehouseContainer.show();

    } else {
        $singleContainer.hide();
        $variantContainer.show();

        // Disable and clear single variant fields
        $singleContainer.find("input, select").prop("disabled", true);
        $variantContainer.find("input, select").prop("disabled", false);
        $warehouseContainer.hide();
    }
}

toggleContainers();

$singleRadio.on("change", toggleContainers);
$variantRadio.on("change", toggleContainers);

$(document).on("click", ".add-variant-btn", function (e) {
    e.preventDefault();

    let index = $("#product-data tr").length;
    var canSeePrice = $("#canSeePrice").val() == "1";
    let permissions = {};
    const warehouseInput = $('#warehouses-data');

    $(".module-permission").each(function () {
        permissions[$(this).data("key")] = $(this).val() == "1";
    });

    let newRow = "<tr>";

    if (permissions.show_batch_no) {
        newRow += `<td><input type="text" name="stocks[${index}][batch_no]" class="form-control form-control-sm custom-table-input" placeholder="25632"></td>`;
    }

    if (warehouseInput.length) {
        const warehouses = JSON.parse(warehouseInput.val());
            let warehouseOptions = '<option value="">Select</option>';
            warehouses.forEach(function(wh) {
                warehouseOptions += `<option value="${wh.id}">${wh.name}</option>`;
            });

            newRow += `
            <td>
                <select name="stocks[${index}][warehouse_id]" class="form-control table-select w-100 role">
                    ${warehouseOptions}
                </select>
                <span></span>
            </td>`;
    }

    if (permissions.show_product_stock) {
        newRow += `<td><input type="number" step="any" min="0" name="stocks[${index}][productStock]" class="form-control form-control-sm custom-table-input" placeholder="3"></td>`;
    }
    if (canSeePrice) {
        if (permissions.show_exclusive_price) {
            newRow += `<td><input type="number" step="any" min="0" name="stocks[${index}][exclusive_price]" class="form-control form-control-sm custom-table-input exclusive_price" placeholder="Ex: 50"></td>`;
        }
        if (permissions.show_inclusive_price) {
            newRow += `<td><input type="number" step="any" min="0" name="stocks[${index}][inclusive_price]" class="form-control form-control-sm custom-table-input inclusive_price" placeholder="Ex: 50"></td>`;
        }
        if (permissions.show_profit_percent) {
            newRow += `<td><input type="number" step="any" name="stocks[${index}][profit_percent]" class="form-control form-control-sm custom-table-input profit_percent" placeholder="25%"></td>`;
        }
    }
    if (permissions.show_product_sale_price) {
        newRow += `<td><input type="number" step="any" min="0" name="stocks[${index}][productSalePrice]" class="form-control form-control-sm custom-table-input productSalePrice" placeholder="Ex: 200"></td>`;
    }
    if (permissions.show_product_wholesale_price) {
        newRow += `<td><input type="number" step="any" min="0" name="stocks[${index}][productWholeSalePrice]" class="form-control form-control-sm custom-table-input" placeholder="Ex: 200"></td>`;
    }
    if (permissions.show_product_dealer_price) {
        newRow += `<td><input type="number" step="any" min="0" name="stocks[${index}][productDealerPrice]" class="form-control form-control-sm custom-table-input" placeholder="Ex: 200"></td>`;
    }
    if (permissions.show_mfg_date) {
        newRow += `<td><input type="date" name="stocks[${index}][mfg_date]" class="form-control"></td>`;
    }
    if (permissions.show_expire_date) {
        newRow += `<td><input type="date" name="stocks[${index}][expire_date]" class="form-control"></td>`;
    }
    if (permissions.show_action) {
        newRow += `<td><a href="#" class="text-danger remove-row"><i class="fal fa-times fa-lg"></i></a></td>`;
    }

    newRow += "</tr>";

    $("#product-data").append(newRow);
});

// remove row
$(document).on("click", ".remove-row", function (e) {
    e.preventDefault();
    $(this).closest("tr").remove();
});

// Calculation logic

// Get VAT rate
function getVatRate() {
    let $activeContainer = $singleRadio.is(":checked")
        ? $singleContainer
        : $variantContainer;
    return (
        parseFloat(
            $activeContainer.find("#vat_id option:selected").data("vat_rate")
        ) || 0
    );
}

// Get VAT type (inclusive/exclusive)
function getVatType() {
    let $activeContainer = $singleRadio.is(":checked")
        ? $singleContainer
        : $variantContainer;
    return $activeContainer.find("#vat_type").val();
}

// Get profit calculation type (markup/margin)
function getProfitOption() {
    return $("#profit_option").val();
}

// Update inclusive_price field based on VAT
function updateInclusiveFromExclusive($row) {
    const vatRate = getVatRate();
    const exclusiveInput = $row.find(".exclusive_price");
    const inclusiveInput = $row.find(".inclusive_price");
    let exclusive = parseFloat(exclusiveInput.val()) || 0;

    // Always add VAT to exclusive price
    inclusiveInput.val((exclusive + (exclusive * vatRate) / 100).toFixed(2));
}


// Calculate MRP from cost and profit
function calculateMrpRow($row) {
    const vatRate = getVatRate();
    const vatType = getVatType();
    const profitOption = getProfitOption();

    const costInput = $row.find(".exclusive_price");
    const profitInput = $row.find(".profit_percent");
    const saleInput = $row.find(".productSalePrice");

    let cost = parseFloat(costInput.val()) || 0;
    let profit = parseFloat(profitInput.val()) || 0;

    updateInclusiveFromExclusive($row);

    if (cost > 0) {
        let basePrice = cost;

        if (vatType === "inclusive") {
            basePrice += (cost * vatRate) / 100;
        }

        let mrp = 0;
        if (profitOption === "margin") {
            mrp = basePrice / (1 - profit / 100);
        } else {
            mrp = basePrice + (basePrice * profit) / 100;
        }

        saleInput.val(mrp.toFixed(2));
    }
}

// Calculate profit from MRP
function calculateProfitFromMrp($row) {
    const vatRate = getVatRate();
    const vatType = getVatType();
    const profitOption = getProfitOption();

    const costInput = $row.find(".exclusive_price");
    const profitInput = $row.find(".profit_percent");
    const saleInput = $row.find(".productSalePrice");

    let cost = parseFloat(costInput.val()) || 0;
    let mrp = parseFloat(saleInput.val()) || 0;

    updateInclusiveFromExclusive($row);

    if (cost > 0 && mrp > 0) {
        let basePrice = cost;

        if (vatType === "inclusive") {
            basePrice += (cost * vatRate) / 100;
        }

        let profit = 0;
        if (profitOption === "margin") {
            profit = ((mrp - basePrice) / mrp) * 100;
        } else {
            profit = ((mrp - basePrice) / basePrice) * 100;
        }

        profitInput.val(profit.toFixed(2));
    }
}

function updateExclusiveFromInclusive($row) {
    const vatRate = getVatRate();

    const inclusiveInput = $row.find(".inclusive_price");
    const exclusiveInput = $row.find(".exclusive_price");

    let inclusive = parseFloat(inclusiveInput.val()) || 0;

    // Reverse VAT: exclusive = inclusive / (1 + VAT%)
    let exclusive = inclusive / (1 + vatRate / 100);
    exclusiveInput.val(exclusive.toFixed(2));

    // Recalculate MRP and profit
    calculateMrpRow($row);
}

// Bind events for real-time calculation
function bindMrpCalculation() {
    $(document).on(
        "input change",
        ".exclusive_price, .profit_percent",
        function () {
            const $row = $(this).closest("tr").length
                ? $(this).closest("tr")
                : $(this).closest(".row");
            calculateMrpRow($row);
        }
    );

    $(document).on("input change", ".productSalePrice", function () {
        const $row = $(this).closest("tr").length
            ? $(this).closest("tr")
            : $(this).closest(".row");
        calculateProfitFromMrp($row);
    });

    $("#vat_id, #vat_type").on("change", function () {
        $(".exclusive_price").each(function () {
            const $row = $(this).closest("tr").length
                ? $(this).closest("tr")
                : $(this).closest(".row");
            calculateMrpRow($row);
        });
    });

    // On inclusive price change, update exclusive and MRP after edit
    $(document).on("blur", ".inclusive_price", function () {
        const $row = $(this).closest("tr").length
            ? $(this).closest("tr")
            : $(this).closest(".row");
        updateExclusiveFromInclusive($row);
    });
}

bindMrpCalculation();
