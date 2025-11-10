<?php
// footer.php - Admin Footer
?>
<style>
    .admin-footer {
        background: #f8f9fa;
        border-top: 1px solid #e0e0e0;
        padding: 1.5rem 2rem;
        margin-top: auto;
    }
    
    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #666;
        font-size: 0.9rem;
    }
    
    .footer-links {
        display: flex;
        gap: 20px;
    }
    
    .footer-link {
        color: #388e3c;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .footer-link:hover {
        color: #2e7d32;
        text-decoration: underline;
    }
</style>

<footer class="admin-footer">
    <div class="footer-content">
        <div class="footer-copyright">
            &copy; <?php echo date('Y'); ?> Farm Management System. All rights reserved.
        </div>
        <div class="footer-links">
            <a href="#" class="footer-link">Privacy Policy</a>
            <a href="#" class="footer-link">Terms of Service</a>
            <a href="#" class="footer-link">Support</a>
        </div>
    </div>
</footer>

<script>
// Common JavaScript functions for admin pages
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
</body>
</html>