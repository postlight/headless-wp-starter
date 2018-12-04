const express = require("express");
const next = require("next");
const favicon = require('serve-favicon');
const path = require('path');

const dev = process.env.NODE_ENV !== "production";
const app = next({ dev });
const handle = app.getRequestHandler();

// Listen to the App Engine-specified port, or 3000 otherwise
const PORT = process.env.PORT || 3000;

app
    .prepare()
    .then(() => {
        const server = express();

        server.use(favicon(path.join(__dirname, 'static', 'favicon.ico')));

        server.get("/*", (req, res) => {
            // redirect some pages to their first children
            // must also edit src/util.js for client-side redirect
            if (req.path.match(/^\/about\/?$/)) {
                res.redirect('/about/biography/');
            }
            if (req.path.match(/^\/education\/?$/)) {
                res.redirect('/about/workshops/');
            }

            // index.js : homepage
            // work.js  : repertory work individual page
            // page.js  : all other pages

            let slug = getSlug(req.path)
            let apiRoute = 'page'
            let templateFile = '/page'

            if (req.path === '/') {
                slug = 'welcome'
                templateFile = '/welcome'
            }

            // individual repertory work page
            if (req.path.indexOf('/current-repertory/') === 0 && req.path !== '/current-repertory/') {
                apiRoute = 'work'
                templateFile = '/work'
            }

            const queryParams = { slug, apiRoute };
            app.render(req, res, templateFile, queryParams);
        });

        server.listen(PORT, err => {
            if (err) throw err;
            console.log(`> Ready on port ${PORT}`);
        });
    })
    .catch(ex => {
        console.error(ex.stack);
        process.exit(1);
    });

function getSlug(url) {
    const parts = url.split("/");
    return parts.pop() || parts.pop();
}
