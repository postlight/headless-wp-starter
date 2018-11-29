const express = require("express");
const next = require("next");

const dev = process.env.NODE_ENV !== "production";
const app = next({ dev });
const handle = app.getRequestHandler();

// Listen to the App Engine-specified port, or 3000 otherwise
const PORT = process.env.PORT || 3000;

app
    .prepare()
    .then(() => {
        const server = express();

        server.get("/*", (req, res) => {

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
