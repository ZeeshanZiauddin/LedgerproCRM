document.addEventListener("DOMContentLoaded", function () {
    const updateTotals = () => {
        let totalSale = 0;
        let totalCost = 0;
        let totalTax = 0;

        // Loop through all rows in the repeater
        document.querySelectorAll(".table-repeater-row").forEach((row) => {
            const sale = parseFloat(
                row.querySelector('[data-field="sale"] input')?.value || 0
            );
            const cost = parseFloat(
                row.querySelector('[data-field="cost"] input')?.value || 0
            );
            const tax = parseFloat(
                row.querySelector('[data-field="tax"] input')?.value || 0
            );

            // Calculate margin for the current row
            const margin = sale - (cost + tax);
            const marginField = row.querySelector(
                '[data-field="margin"] input'
            );
            if (marginField) {
                marginField.value = margin.toFixed(2); // Update the margin field in the same row
            }

            // Add values to the totals
            totalSale += sale;
            totalCost += cost;
            totalTax += tax;
        });

        // Update the total fields outside the repeater
        const totalSaleField = document.querySelector(
            '[data-field="total_sale"] input'
        );
        const totalCostField = document.querySelector(
            '[data-field="total_cost"] input'
        );
        const totalTaxField = document.querySelector(
            '[data-field="total_tax"] input'
        );
        const totalMarginField = document.querySelector(
            '[data-field="total_margin"] input'
        );
        if (totalSaleField) totalSaleField.value = totalSale.toFixed(2);
        if (totalCostField) totalCostField.value = totalCost.toFixed(2);
        if (totalTaxField) totalTaxField.value = totalTax.toFixed(2);
        if (totalMarginField) {
            totalMarginField.value = (
                totalSale -
                (totalCost + totalTax)
            ).toFixed(2);
        }
    };

    const bindEventListeners = () => {
        document
            .querySelectorAll(".table-repeater-row [data-field] input")
            .forEach((input) => {
                input.removeEventListener("input", updateTotals); // Prevent duplicate listeners
                input.addEventListener("input", updateTotals); // Attach new listener
            });
    };

    // Trigger calculation and rebind listeners when new rows are added
    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            if (
                mutation.type === "childList" &&
                mutation.addedNodes.length > 0
            ) {
                bindEventListeners();
                updateTotals(); // Recalculate totals for new rows
            }
        }
    });

    const repeaterContainer = document.querySelector(
        "#-passengers-tab .table-repeater-rows-wrapper"
    );

    if (repeaterContainer) {
        console.log("Repeator container Found");
        observer.observe(repeaterContainer, { childList: true, subtree: true });
    }

    // Initial binding and calculation
    bindEventListeners();
    updateTotals();
});
