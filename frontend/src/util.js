import React from 'react';
import Link from "next/link";
import urlParse from 'url-parse';

export const createLink = (item, index) => {
  if (item.link) {
    return (
      <Link
        prefetch
        as={`${getLocation(item.link)}`}
        href={`/page?slug=${item.slug}&apiRoute=page`}
        key={item.id}
      >
        <a>{item.title.rendered}</a>
      </Link>
    )
  }

  let slug = getSlug(item.url);
  let asSlug = slug
  if (asSlug === "about") {
    asSlug = "about/biography"
    slug = "biography"
  }

  return (
    <Link
      prefetch
      as={`/${asSlug}/`}
      href={`/page?slug=${slug}&apiRoute=${item.object}`}
      key={index}
    >
      <a className="nav-link" onClick={clickMenuItem}>{item.title}</a>
    </Link>
  );
}

function clickMenuItem() {
  window.setTimeout(() => {
    $('.navbar-collapse.show').length && $('.navbar-toggler').trigger('click');
  }, 50);
}

function getLocation(href) {
  const url = urlParse(href);
  return url.pathname;
};

function getSlug(url) {
  const parts = url.split("/");
  return parts.length > 2 ? parts[parts.length - 2] : "";
}