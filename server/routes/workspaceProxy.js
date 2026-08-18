const express = require('express');
const router = express.Router();

// Since the user is developing locally, default to the typical local Laravel ports
// if WORKSPACE_API_URL isn't explicitly set in the .env file.
const WORKSPACE_API_URL = process.env.WORKSPACE_API_URL || 'https://antiquewhite-meerkat-394750.hostingersite.com';

// Catch-all middleware to proxy requests to the workspace API
router.use(async (req, res) => {
    try {
        // req.originalUrl could contain /api/workspace/..., so we need just the relative path
        // Since we mounted this at /api/workspace, req.url is the path *after* that.
        // e.g. /users
        const targetUrl = `${WORKSPACE_API_URL}${req.url}`;
        
        console.log(`[Workspace Proxy] Forwarding ${req.method} request to ${targetUrl}`);

        // Forward headers, omitting host to avoid SSL/DNS mismatches
        const headers = { 
            ...req.headers,
            'X-ERP-SECRET': 'default-erp-secret-12345'
        };
        delete headers.host;

        const fetchOptions = {
            method: req.method,
            headers: headers,
        };

        // If it's a POST, PUT, or PATCH, forward the body
        if (['POST', 'PUT', 'PATCH'].includes(req.method)) {
            fetchOptions.body = JSON.stringify(req.body);
            // Ensure content-type is application/json since we stringified the body
            fetchOptions.headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(targetUrl, fetchOptions);
        const data = await response.json().catch(() => null);

        // Send back the response from the external workspace
        res.status(response.status).json(data || { message: 'Received non-JSON response from workspace' });

    } catch (error) {
        console.error('[Workspace Proxy] Error connecting to workspace:', error.message);
        res.status(500).json({ success: false, message: 'Failed to connect to the external workspace API.' });
    }
});

module.exports = router;
