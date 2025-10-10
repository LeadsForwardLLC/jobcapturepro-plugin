document.addEventListener("DOMContentLoaded", function () {
    // Get grid ID from localized data
    const gridId = jcpGridData.gridId;
    const grid = document.querySelector("." + gridId);

    if (!grid) return;

    // Function to detect column count from CSS
    function getColumnCount() {
        const style = window.getComputedStyle(grid);
        const columnCount = style.getPropertyValue("column-count");
        return parseInt(columnCount) || 4; // Default to 4 if not set
    }

    // Force items to be added in correct order for visual masonry
    function rearrangeItems() {
        const items = Array.from(grid.children);

        // First remove all items
        items.forEach(item => grid.removeChild(item));

        // Calculate column count
        const columnCount = getColumnCount();

        // Update grid attribute with current column count
        grid.setAttribute("data-column-count", columnCount);

        // Only keep items that fit evenly into columns
        // This ensures the masonry layout works correctly
        const itemsToKeep = Math.floor(items.length / columnCount) * columnCount;
        const finalItems = items; //.slice(0, itemsToKeep); // TODO: just need a better algo for sorting the masonry grid

        // Create "virtual" columns - these will help us rearrange items properly
        const columns = Array.from({ length: columnCount }, () => []);

        // Organize items by column (this ensures ordered reading left-to-right)
        items.forEach((item, index) => {
            const columnIndex = index % columnCount;
            columns[columnIndex].push(item);
        });

        // Add back to grid in column-first order
        columns.forEach(column => {
            column.forEach(item => {
                grid.appendChild(item);
            });
        });
    }

    // Run on load
    rearrangeItems();

    // Also run when window is resized (column count may change)
    let previousColumnCount = getColumnCount();
    window.addEventListener("resize", function () {
        const newColumnCount = getColumnCount();
        if (newColumnCount !== previousColumnCount) {
            previousColumnCount = newColumnCount;
            rearrangeItems();
        }
    });
});