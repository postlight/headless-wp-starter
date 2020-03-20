const express = require('express')
const app = express()
const port = 3001

app.use(express.static('build'))

app.listen(port, () => console.log(`Landing page now listening on ${port}`))