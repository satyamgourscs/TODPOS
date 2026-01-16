const dropdown = document.getElementById("myDropdown");
const selected = dropdown.querySelector("#selectedOption");
const options = dropdown.querySelector("#dropdownOptions");
const arrow = dropdown.querySelector("#dropdownArrow");
const items = dropdown.querySelectorAll(".dropdown-option");

// hidden input to send batch_no
const batchInput = document.getElementById("selected_batch_no");

selected.addEventListener("click", () => {
    const isOpen = options.style.display === "block";
    options.style.display = isOpen ? "none" : "block";
    dropdown.classList.toggle("open", !isOpen);
});

items.forEach((item) => {
    item.addEventListener("click", () => {
        // update display text
        selected.querySelector("span").innerHTML = item.innerHTML;

        // update hidden input with selected batch_no
        const batchNo = item.getAttribute("data-batch-no");
        batchInput.value = batchNo;

        options.style.display = "none";
        dropdown.classList.remove("open");
    });
});

document.addEventListener("click", function (e) {
    if (!dropdown.contains(e.target)) {
        options.style.display = "none";
        dropdown.classList.remove("open");
    }
});
