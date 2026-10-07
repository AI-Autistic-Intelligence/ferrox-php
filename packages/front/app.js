document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-links li');
    const currentModule = document.getElementById('current-module');
    const mfeRoot = document.getElementById('mfe-root');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // Remove active class
            navLinks.forEach(l => l.classList.remove('active'));
            // Add active class
            const target = e.currentTarget;
            target.classList.add('active');

            const moduleName = target.getAttribute('data-mfe');
            currentModule.textContent = target.textContent;

            // Microfrontend Loader Simulation
            loadMicrofrontend(moduleName);
        });
    });

    function loadMicrofrontend(module) {
        // In a real MFE architecture, this would load a remote Entry point via Module Federation
        mfeRoot.style.opacity = 0;
        
        setTimeout(() => {
            if (module === 'dlq') {
                mfeRoot.innerHTML = `
                    <div class="hero-dashboard">
                        <h1>Dead Letter Queue Resolution</h1>
                        <p>Events failed during ERP (Itsperfect) webhook synchronization.</p>
                        <div class="glass-card" style="margin-top: 2rem; border-left: 4px solid var(--danger);">
                            <h3>Event ID: EVT-9921-X</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1rem;">Payload: { order_id: "ORD-102", action: "stock_decrement", retries: 5 }</p>
                            <div class="action-btn">Re-dispatch to CommandBus</div>
                        </div>
                    </div>
                `;
            } else if (module === 'warehouse') {
                mfeRoot.innerHTML = `
                    <div class="hero-dashboard">
                        <h1>POS Offline-First Sync</h1>
                        <p>Local IndexDB storage active. 0 pending mutations.</p>
                        <div class="glass-grid" style="margin-top: 2rem;">
                            <div class="glass-card">
                                <h3>Pending Commits</h3>
                                <div class="big-number">0</div>
                                <div class="trend positive">All synced with Master DB</div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                mfeRoot.innerHTML = `
                    <div class="hero-dashboard">
                        <h1>Module Loading...</h1>
                        <p>Microfrontend [${module}] requested via Module Federation.</p>
                    </div>
                `;
            }
            mfeRoot.style.opacity = 1;
        }, 300); // Simulate network delay
    }
});
