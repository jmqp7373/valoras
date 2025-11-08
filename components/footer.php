<?php
/**
 * Footer Component - Valora.vip
 * Componente reutilizable para el pie de página del dashboard
 */
?>
<footer class="dashboard-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>Valora.vip</h4>
            <p>Plataforma de gestión empresarial</p>
        </div>
        
        <div class="footer-section">
            <h4>Enlaces</h4>
            <ul>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php">Dashboard</a></li>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>views/finanzas/finanzasDashboard.php">Finanzas</a></li>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>views/tickets/ticketList.php">Tickets</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Soporte</h4>
            <ul>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>views/tickets/ticketCreate.php">Crear Ticket</a></li>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>views/admin/checksTests/system-check.php">Verificación Sistema</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> Valora.vip - Todos los derechos reservados
            </p>
        </div>
    </div>
</footer>

<style>
    .dashboard-footer {
        background-color: #1B263B;
        color: white;
        padding: 2rem 0;
        margin-top: 3rem;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }

    .footer-section h4 {
        color: #ee6f92;
        margin-bottom: 1rem;
        font-family: 'Poppins', sans-serif;
        font-size: 1.1rem;
    }

    .footer-section p {
        color: #E5E5E5;
        line-height: 1.6;
        font-family: 'Poppins', sans-serif;
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-section ul li {
        margin-bottom: 0.5rem;
    }

    .footer-section ul li a {
        color: #E5E5E5;
        text-decoration: none;
        transition: color 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .footer-section ul li a:hover {
        color: #ee6f92;
    }

    .footer-copyright {
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #999;
    }

    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .footer-section {
            margin-bottom: 1rem;
        }
    }
</style>
