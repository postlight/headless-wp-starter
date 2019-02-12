/* eslint-disable jsx-a11y/anchor-is-valid */
import React from 'react';
import Link from 'next/link';

const linkStyle = {
  marginRight: 15,
};

const getSlug = url => {
  const parts = url.split('/');
  return parts.length > 2 ? parts[parts.length - 2] : '';
};

const Menu = props => {
  const { menu } = props;
  const menuItems = menu.items.map(item => {
    if (item.object === 'custom') {
      return (
        <Link href={item.url} key={item.ID}>
          <a style={linkStyle}>{item.title}</a>
        </Link>
      );
    }
    const slug = getSlug(item.url);
    const actualPage = item.object === 'category' ? 'category' : 'post';
    return (
      <Link
        as={`/${item.object}/${slug}`}
        href={`/${actualPage}?slug=${slug}&apiRoute=${item.object}`}
        key={item.ID}
      >
        <a style={linkStyle}>{item.title}</a>
      </Link>
    );
  });

  return (
    <div>
      <Link href="/">
        <a style={linkStyle}>Home</a>
      </Link>
      {menuItems}
    </div>
  );
};

export default Menu;
