// UI/assets/js/script.js

document.addEventListener("DOMContentLoaded", () => {
    // Handle navigation buttons
    const updateFarmBtn = document.getElementById("updateFarmBtn");
    const addProductBtn = document.getElementById("addProductBtn");
    const viewSalesBtn = document.getElementById("viewSalesBtn");
    const viewNotificationsBtn = document.getElementById("viewNotificationsBtn");
    const logoutBtn = document.getElementById("logoutBtn");

    if (updateFarmBtn) {
        updateFarmBtn.addEventListener("click", () => {
            window.location.href = "update_farm.php";
        });
    }

    if (addProductBtn) {
        addProductBtn.addEventListener("click", () => {
            window.location.href = "add_product.php";
        });
    }

    if (viewSalesBtn) {
        viewSalesBtn.addEventListener("click", () => {
            window.location.href = "view_sales.php";
        });
    }

    if (viewNotificationsBtn) {
        viewNotificationsBtn.addEventListener("click", () => {
            window.location.href = "notifications.php";
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            fetch("../controllers/AuthController.php?action=logout")
                .then(() => {
                    window.location.href = "login.php";
                })
                .catch(err => console.error("Logout failed:", err));
        });
    }
});
