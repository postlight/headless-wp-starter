import React from 'react'

const Shop = ({categories}) => {
  console.log(categories)
  return (
    <div id="shop">
      {categories.map((category, i) =>
        <div className="category" key={i}>
          <h1>{category.name}</h1>
          {category.products.map((product, j) =>
            <div className="product" key={j}>
              <img src={product.image.sizes.thumbnail} alt={product.name} />
              <div className="product-name">{product.name}</div>
              <div className="product-subtitle">{product.subtitle}</div>
              <ul>
                {product.purchase_links.map((link, k) => 
                  <li>{prettyLink(link.link)}</li>
                )}
              </ul>
            </div>
          )}
        </div>
      )}
    </div>
  )
}

function prettyLink(link) {
  return link
}

export default Shop;
