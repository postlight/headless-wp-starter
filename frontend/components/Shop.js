import React from 'react'
import urlParse from 'url-parse'
import safeGet from "lodash/get";

const Shop = ({categories}) => {
  return (
    <div id="shop">
      {categories.map((category, i) =>
        <div className="category" key={i}>
          <h1>{category.name}</h1>
          <div className="products">
            {category.products.map((product, j) =>
              <div className="product card m-2" key={j}>
                <img className="card-img-top" src={safeGet(product, 'image.sizes.thumbnail')} alt={product.name} />
                <div className="card-body">
                  <div className="product-name card-text">{product.name}</div>
                  <div className="product-subtitle card-text">{product.subtitle}</div>
                  <ul className="product-purchase-links">
                    {product.purchase_links.map((link, k) => 
                      <li key={k}>{prettyLink(link.link)}</li>
                    )}
                  </ul>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  )
}

function prettyLink(link) {
  const hostname = urlParse(link).hostname
  let linkText = `Purchase at ${hostname}`

  if (hostname.match(/amazon/)) {
    linkText = 'Purchase on Amazon.com'
  }

  if (hostname.match(/itunes/)) {
    linkText = 'Download from iTunes'
  }

  if (hostname.match(/paypal/)) {
    linkText = 'Pay with PayPal'
  }

  return (
    <a href={link} target="_blank">{linkText}</a>
  )
}

export default Shop;
