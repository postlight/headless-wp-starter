const express = require("express");
const next = require("next");

const dev = process.env.NODE_ENV !== "production";
const app = next({ dev });
const handle = app.getRequestHandler();

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

        server.listen(3000, err => {
            if (err) throw err;
            console.log("> Ready on http://localhost:3000");
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