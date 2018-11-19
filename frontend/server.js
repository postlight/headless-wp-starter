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
            // page.js  : all other pages
            const templateFile = req.path === '/' ?
                '/index' :
                '/page';

            const queryParams = { slug: req.path === '/' ? 'welcome' : getSlug(req.path), apiRoute: "page" };
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
